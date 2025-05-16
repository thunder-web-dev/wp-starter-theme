/**
 * Основной файл сервера Express.
 * - Настраивает CORS, JSON-парсинг.
 * - Создаёт отдельный роутер для API.
 * - Динамически регистрирует и обновляет маршруты из папок db и forms при изменении файлов.
 */

import express from "express";
import cors from "cors";
import path from "path";
import chokidar from "chokidar";
import { fileURLToPath } from "url";
import { registerDbRoutes } from "./src/routes/dbRoutes.js";
import { registerFormRoutes } from "./src/routes/formRoutes.js";

// Получаем __dirname в ESM
const moduleUrl = import.meta.url;
const __filename = fileURLToPath(moduleUrl);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = 5000;

app.use(express.json());
app.use(express.urlencoded({ extended: true })); // Добавлено для поддержки form-urlencoded
app.use(cors());

const dbDir = path.join(__dirname, "db");
const formsDir = path.join(__dirname, "forms");

/**
 * Экземпляр роутера для обработки всех API-запросов.
 * Все маршруты будут зарегистрированы на этом роутере и монтированы на '/api'.
 * @type {import('express').Router}
 */
const apiRouter = express.Router();
app.use("/api", apiRouter);

/**
 * Удаляет все ранее зарегистрированные маршруты внутри apiRouter.
 * Очищает стек middleware и маршрутов, чтобы избежать дублирования при повторной инициализации.
 * @returns {void}
 */
const clearRoutes = () => {
  apiRouter.stack = [];
};

/**
 * Инициализирует (или переинициализирует) маршруты для работы с JSON-файлами и формами.
 * Выполняет:
 *   1. Очистку старых маршрутов через clearRoutes().
 *   2. Регистрацию маршрутов из папки db (GET).
 *   3. Регистрацию маршрутов из папки forms (POST).
 * @async
 * @returns {Promise<void>} Промис, завершающийся после регистрации всех маршрутов.
 */
async function initRoutes() {
  clearRoutes();
  await registerDbRoutes(apiRouter, dbDir);
  await registerFormRoutes(apiRouter, formsDir);
}

// Первоначальная инициализация маршрутов
await initRoutes();

/**
 * Настраивает наблюдателей за папками db и forms.
 * При добавлении, изменении или удалении файлов повторно инициализирует маршруты.
 * @private
 */
const watchers = [chokidar.watch(dbDir), chokidar.watch(formsDir)];
watchers.forEach((watcher) => {
  watcher
    .on("add", initRoutes)
    .on("change", initRoutes)
    .on("unlink", initRoutes);
});

app.get("/", (req, res) => {
  res.send("Сервер запущен. Используйте /api для доступа к API.");
});

/**
 * Запускает HTTP-сервер на указанном порту.
 */
app.listen(PORT, () => {
  console.log(`Сервер запущен на http://localhost:${PORT}`);
});
