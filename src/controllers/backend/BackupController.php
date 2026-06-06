<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\controllers\backend;

use Besnovatyj\Wage\services\backup\BackupService;
use DateTimeImmutable;
use InvalidArgumentException;
use Throwable;
use Yii;
use yii\base\Module;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\RangeNotSatisfiableHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Контроллер резервного копирования и восстановления данных квитков.
 */
class BackupController extends Controller
{
    public function __construct(
        string                     $id,
        Module                     $module,
        private readonly BackupService $backupService,
        array                      $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'export' => ['GET'],
                    'import' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Страница управления бэкапами.
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * Скачивает JSON-файл со всеми квитками.
     * @throws RangeNotSatisfiableHttpException
     */
    public function actionExport(): Response
    {
        try {
            $json     = $this->backupService->export();
            $filename = 'wage-backup-' . new DateTimeImmutable()->format('Y-m-d_H-i-s') . '.json';
        } catch (Throwable $e) {
            Yii::$app->session->setFlash('error', 'Ошибка экспорта: ' . $e->getMessage());
            return $this->redirect(['index']);
        }

        return Yii::$app->response->sendContentAsFile(
            $json,
            $filename,
            ['mimeType' => 'application/json', 'inline' => false]
        );
    }

    /**
     * Принимает JSON-файл и восстанавливает из него данные.
     * Существующие периоды пропускаются.
     */
    public function actionImport(): Response
    {
        $file = UploadedFile::getInstanceByName('backup_file');

        if ($file === null || $file->error !== UPLOAD_ERR_OK) {
            Yii::$app->session->setFlash('error', 'Файл не загружен или произошла ошибка загрузки.');
            return $this->redirect(['index']);
        }

        if (pathinfo($file->name, PATHINFO_EXTENSION) !== 'json') {
            Yii::$app->session->setFlash('error', 'Допустимы только файлы с расширением .json.');
            return $this->redirect(['index']);
        }

        $content = file_get_contents($file->tempName);
        if ($content === false || $content === '') {
            Yii::$app->session->setFlash('error', 'Не удалось прочитать загруженный файл.');
            return $this->redirect(['index']);
        }

        try {
            $result = $this->backupService->import($content);
        } catch (InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', 'Некорректный файл бэкапа: ' . $e->getMessage());
            return $this->redirect(['index']);
        } catch (Throwable $e) {
            Yii::$app->session->setFlash('error', 'Ошибка при восстановлении: ' . $e->getMessage());
            return $this->redirect(['index']);
        }

        $msg = "Восстановлено квитков: {$result->imported}.";
        if ($result->skipped > 0) {
            $msg .= " Пропущено (период уже существует): {$result->skipped}"
                . ' (' . implode(', ', $result->skippedPeriods) . ').';
        }

        Yii::$app->session->setFlash($result->skipped > 0 ? 'warning' : 'success', $msg);

        return $this->redirect(['index']);
    }
}
