import gulpif from 'gulp-if';
import webp from 'gulp-webp';
import path from 'path';
import { readdirSync, statSync, existsSync } from 'fs';
import { deleteAsync } from 'del';

export const images = () => {
  const shouldConvertToWebp = (file) => {
    const ext = path.extname(file.path).toLowerCase();
    const basename = path.basename(file.path, ext);

    // Исключаем SVG файлы
    if (ext === '.svg') {
      return false;
    }

    // Исключаем файлы с _keep перед расширением
    if (basename.endsWith('_keep')) {
      return false;
    }

    // Конвертируем jpg, jpeg, png
    return ['.jpg', '.jpeg', '.png'].includes(ext);
  };

  // Нормализует путь для кроссплатформенности (приводит к формату с прямыми слешами)
  const normalizePath = (filePath) => {
    return filePath.replace(/\\/g, '/');
  };

  // Получаем список всех файлов в директории рекурсивно
  const getAllFiles = (dir, baseDir = dir) => {
    const files = [];
    if (!existsSync(dir)) return files;

    const items = readdirSync(dir);
    for (const item of items) {
      const fullPath = path.join(dir, item);
      const stat = statSync(fullPath);

      if (stat.isDirectory()) {
        files.push(...getAllFiles(fullPath, baseDir));
      } else {
        const relativePath = path.relative(baseDir, fullPath);
        files.push(normalizePath(relativePath));
      }
    }
    return files;
  };

  // Определяем ожидаемые файлы в билде на основе исходных файлов
  const getExpectedBuildFiles = (srcFiles) => {
    const expected = new Set();

    for (const srcFile of srcFiles) {
      const normalizedSrcFile = normalizePath(srcFile);
      const ext = path.extname(normalizedSrcFile).toLowerCase();
      const basename = path.basename(normalizedSrcFile, ext);
      const dir = path.dirname(normalizedSrcFile);
      const dirPrefix = dir !== '.' ? `${dir}/` : '';

      // Пропускаем SVG файлы из папки svg - они идут в спрайт
      // Остальные SVG копируются как есть
      if (ext === '.svg') {
        // Проверяем, что это НЕ файл из папки svg
        if (!normalizedSrcFile.includes('/svg/')) {
          expected.add(normalizedSrcFile);
        }
        continue;
      }

      // Файлы с _keep остаются с исходным расширением
      if (basename.endsWith('_keep')) {
        expected.add(normalizedSrcFile);
      }
      // Остальные jpg/jpeg/png конвертируются в webp
      else if (['.jpg', '.jpeg', '.png'].includes(ext)) {
        const webpFile = `${dirPrefix}${basename}.webp`;
        expected.add(webpFile);
      }
    }

    return expected;
  };

  // Удаляем файлы из билда, которых нет в исходной папке
  const cleanupDeletedFiles = async () => {
    const srcFiles = getAllFiles(app.paths.srcImgFolder);
    const expectedBuildFiles = getExpectedBuildFiles(srcFiles);
    const buildFiles = getAllFiles(app.paths.buildImgFolder)
      .map(file => normalizePath(file))
      .filter(file => !file.includes('sprite.svg'));

    const filesToDelete = [];
    for (const buildFile of buildFiles) {
      if (!expectedBuildFiles.has(buildFile)) {
        const fullPath = path.join(app.paths.buildImgFolder, buildFile);
        filesToDelete.push(fullPath);
      }
    }

    if (filesToDelete.length > 0) {
      await deleteAsync(filesToDelete);
    }
  };

  // Копируем только файлы с _keep (они не конвертируются в webp)
  const keepStream = app.gulp.src([`${app.paths.srcImgFolder}/**/*_keep.{jpg,jpeg,png}`], { encoding: false })
    .pipe(app.gulp.dest(app.paths.buildImgFolder));

  // Конвертируем остальные jpg/jpeg/png файлы в webp
  const webpStream = app.gulp.src([`${app.paths.srcImgFolder}/**/*.{jpg,jpeg,png}`], { encoding: false })
    .pipe(gulpif(shouldConvertToWebp, webp()))
    .pipe(app.gulp.dest(app.paths.buildImgFolder));

  // Копируем SVG файлы, которые НЕ из папки svg (они идут в спрайт)
  // SVG из папки svg обрабатываются задачей svgSprites
  const svgStream = app.gulp.src([
    `${app.paths.srcImgFolder}/**/*.svg`,
    `!${app.paths.srcImgFolder}/svg/**/*.svg`
  ], { encoding: false })
    .pipe(app.gulp.dest(app.paths.buildImgFolder));

  // После завершения всех потоков очищаем удаленные файлы
  return new Promise((resolve, reject) => {
    let completed = 0;
    const totalStreams = 3;
    const onComplete = () => {
      completed++;
      if (completed === totalStreams) {
        cleanupDeletedFiles()
          .then(() => resolve())
          .catch(reject);
      }
    };

    keepStream.on('end', onComplete);
    webpStream.on('end', onComplete);
    svgStream.on('end', onComplete);
    keepStream.on('error', reject);
    webpStream.on('error', reject);
    svgStream.on('error', reject);
  });
};
