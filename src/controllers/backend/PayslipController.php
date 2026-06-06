<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

declare(strict_types=1);

namespace Besnovatyj\Wage\controllers\backend;

use Besnovatyj\Wage\entities\Payslip;
use Besnovatyj\Wage\forms\backend\PayslipForm;
use Besnovatyj\Wage\forms\backend\search\PayslipSearch;
use Besnovatyj\Wage\repositories\NotFoundException;
use Besnovatyj\Wage\services\manage\PayslipManageService;
use DomainException;
use Throwable;
use Yii;
use yii\base\Module;
use yii\filters\VerbFilter;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Контроллер CRUD-операций над зарплатными квитками.
 */
class PayslipController extends Controller
{
    public function __construct(
        string                           $id,
        Module                 $module,
        private readonly PayslipManageService $service,
        array                            $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Список квитков (сводный грид).
     */
    public function actionIndex(): string
    {
        $searchModel  = new PayslipSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * Просмотр квитка.
     *
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'payslip' => $this->findModel($id),
        ]);
    }

    /**
     * Создание нового квитка.
     */
    public function actionCreate(): Response|string
    {
        $form = new PayslipForm();

        if (Yii::$app->request->isPost) {
            $form->load(Yii::$app->request->post());
            $form->loadItems(Yii::$app->request->post('PayslipItemForm', []));

            if ($form->validate()) {
                try {
                    $payslip = $this->service->create($form);
                    Yii::$app->session->setFlash('success', 'Квиток успешно создан.');
                    return $this->redirect(['view', 'id' => $payslip->id]);
                } catch (DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                } catch (Throwable $e) {
                    Yii::$app->errorHandler->logException($e);
                    Yii::$app->session->setFlash('error', YII_DEBUG ? VarDumper::dumpAsString($e) : 'Внутренняя ошибка.');
                }
            }
        }

        return $this->render('create', ['model' => $form]);
    }

    /**
     * Редактирование квитка.
     *
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): Response|string
    {
        $payslip = $this->findModel($id);
        $form    = new PayslipForm($payslip);

        if (Yii::$app->request->isPost) {
            $form->load(Yii::$app->request->post());
            $form->loadItems(Yii::$app->request->post('PayslipItemForm', []));

            if ($form->validate()) {
                try {
                    $this->service->edit($payslip, $form);
                    Yii::$app->session->setFlash('success', 'Квиток успешно обновлён.');
                    return $this->redirect(['view', 'id' => $payslip->id]);
                } catch (DomainException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                } catch (Throwable $e) {
                    Yii::$app->errorHandler->logException($e);
                    Yii::$app->session->setFlash('error', YII_DEBUG ? VarDumper::dumpAsString($e) : 'Внутренняя ошибка.');
                }
            }
        }

        return $this->render('update', ['model' => $form, 'payslip' => $payslip]);
    }

    /**
     * Удаление квитка.
     *
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id): Response
    {
        try {
            $this->service->remove($id);
            Yii::$app->session->setFlash('success', 'Квиток удалён.');
        } catch (NotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (Throwable $e) {
            Yii::$app->errorHandler->logException($e);
            Yii::$app->session->setFlash('error', YII_DEBUG ? VarDumper::dumpAsString($e) : 'Ошибка удаления.');
        }

        return $this->redirect(['index']);
    }

    /**
     * @throws NotFoundHttpException если квиток не найден
     */
    private function findModel(int $id): Payslip
    {
        if (($payslip = Payslip::findOne($id)) !== null) {
            return $payslip;
        }

        throw new NotFoundHttpException('Квиток не найден.');
    }
}
