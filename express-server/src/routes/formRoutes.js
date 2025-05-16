import { promises as fs } from "fs";
import path from "path";
import multer from "multer";

const upload = multer(); // для парсинга multipart/form-data без файлов

/**
 * Регистрирует POST-маршруты для обработки форм, определенных JSON-файлами.
 *
 * Для каждого файла "*.json" в директории formsDir создается маршрут:
 *   POST /forms/{name}
 * где {name} — имя файла без расширения.
 *
 * Валидация тела запроса по описанию полей:
 *   - Поля с флагом required=true должны присутствовать и быть непустыми.
 *   - Поля с validateAs="email" проверяются простым регулярным выражением.
 *
 * В случае ошибок валидации возвращается HTTP 400 с JSON { errors: string[] }.
 * При успешной валидации возвращается JSON { status: "ok", data: req.body }.
 *
 * @param {import('express').Application} app - Экземпляр приложения Express.
 * @param {string} formsDir - Абсолютный путь к директории с JSON-описаниями форм.
 * @returns {Promise<void>} Промис, завершающийся после регистрации всех маршрутов.
 */
export async function registerFormRoutes(app, formsDir) {
  const files = await fs.readdir(formsDir);

  for (const file of files.filter((f) => f.endsWith(".json"))) {
    const name = path.basename(file, ".json");
    const route = `/forms/${name}`;
    const defPath = path.join(formsDir, file);

    /**
     * Обрабатывает POST-запрос для формы {name}.
     * Валидирует обязательность полей и формат email.
     *
     * @param {import('express').Request} req - Объект запроса Express.
     * @param {import('express').Response} res - Объект ответа Express.
     */
    app.post(route, upload.none(), async (req, res) => {
      console.log(req.body);
      let def;
      try {
        def = JSON.parse(await fs.readFile(defPath, "utf-8"));
      } catch {
        return res.status(500).json({ error: "Ошибка чтения формы" });
      }

      const errors = [];
      for (const f of def.fields || []) {
        const v = req.body[f.key];
        if (f.required && (v === undefined || v === "")) {
          errors.push(`"${f.name}" обязательно`);
        }
        if (v != null && f.validateAs === "email") {
          if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
            errors.push(`"${f.name}" невалидный email`);
          }
        }
      }
      if (errors.length) return res.status(400).json({ errors });

      console.log("GOT POST | ", route, req.body);
      console.log(
        "Sending response | ",
        JSON.stringify({ status: "ok", data: req.body })
      );

      res.json({ status: "ok", data: req.body });
    });
  }
}
