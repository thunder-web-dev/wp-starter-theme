import { promises as fs } from "fs";
import path from "path";

/**
 * Регистрирует маршруты GET для работы с JSON-файлами в указанной директории.
 *
 * Для каждого файла \"*.json\" в папке dbDir создаются два маршрута:
 *   1. GET /{name}       — возвращает полный JSON-массив или объект.
 *   2. GET /{name}/:id    — возвращает элемент с полем id === :id из массива.
 *
 * @param {import('express').Application} app - Экземпляр приложения Express.
 * @param {string} dbDir - Абсолютный путь к директории с JSON-файлами базы данных.
 * @returns {Promise<void>} Промис, завершающийся после регистрации всех маршрутов.
 */
export async function registerDbRoutes(app, dbDir) {
  const files = await fs.readdir(dbDir);
  for (const file of files.filter((f) => f.endsWith(".json"))) {
    const name = path.basename(file, ".json");
    const route = `/json/${name}`;
    const filePath = path.join(dbDir, file);

    console.log("GET", route);

    /**
     * Возвращает полный JSON из файла.
     * @name GET /{name}
     */
    app.get(route, async (_, res) => {
      try {
        const data = await fs.readFile(filePath, "utf-8");

        res.json(JSON.parse(data));
      } catch (e) {
        res.status(500).json({ error: e.message });
      }
    });

    /**
     * Возвращает объект по id из JSON-массива.
     * @name GET /{name}/:id
     */
    app.get(`${route}/:id`, async (req, res) => {
      try {
        const json = JSON.parse(await fs.readFile(filePath, "utf-8"));
        const list = json[name] || json;
        const item = list.find((el) => el.id === +req.params.id);
        if (!item) return res.status(404).json({ error: "Не найдено" });
        res.json(item);
      } catch (e) {
        res.status(500).json({ error: e.message });
      }
    });
  }
}
