import express from "express";
import cors from "cors";
import { promises as fsPromises } from "fs";
import path from "path";
import { fileURLToPath } from "url";

const app = express();
const PORT = 5000;

app.use(express.json());
app.use(cors());

const moduleUrl = import.meta.url;
const __filename = fileURLToPath(moduleUrl);
const __dirname = path.dirname(__filename);
const dbDir = path.join(__dirname, "db");

async function registerRoutes() {
  const files = await fsPromises.readdir(dbDir);

  files
    .filter((file) => file.endsWith(".json"))
    .forEach((file) => {
      const name = path.basename(file, ".json");
      const route = `/api/${name}`;
      const filePath = path.join(dbDir, file);

      console.log("Регистрация ", route);

      app.get(route, async (req, res) => {
        try {
          const data = await fsPromises.readFile(filePath, "utf-8");
          const json = JSON.parse(data);

          res.status(200).json(json);
        } catch (err) {
          res.status(500).json(err);
        }
      });

      app.get(`${route}/:id`, async (req, res) => {
        try {
          const data = await fsPromises.readFile(filePath, "utf-8");
          const json = JSON.parse(data);

          const list = json[name] || json;

          const item = list.find((el) => el.id === parseInt(req.params.id, 10));

          if (!item) {
            return res.status(404).json({ error: "Нет с таким id" });
          }

          res.status(200).json(json);
        } catch (err) {
          res.status(500).json(err);
        }
      });
    });
}

registerRoutes().then(() => {
  app.listen(PORT, () => {
    console.log("Сервер запущен на порту " + PORT);
  });
});
