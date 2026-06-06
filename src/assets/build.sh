#!/usr/bin/env bash
#
# Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
#

# Сборка TypeScript ресурсов модуля Wage.
# Требует установленного esbuild: npm install -g esbuild
#
# Запуск: bash build.sh
# Запуск с наблюдением за изменениями: bash build.sh --watch

set -e
cd "$(dirname "$0")"

WATCH=""
if [[ "$1" == "--watch" ]]; then
    WATCH="--watch"
fi

echo "Сборка wage-chart.ts..."
esbuild src/wage-chart.ts \
    --bundle \
    --target=es2020 \
    --outfile=dist/wage-chart.js \
    --platform=browser \
    $WATCH

echo "Сборка wage-form.ts..."
esbuild src/wage-form.ts \
    --bundle \
    --target=es2020 \
    --outfile=dist/wage-form.js \
    --platform=browser \
    $WATCH

echo "Готово."
