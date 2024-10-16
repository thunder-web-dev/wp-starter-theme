<?php
/**
 * Шаблон дефолтной страницы.
 */

ct()->header();
the_post();
?>

	<section class="page-default container">
		<h1><?php the_title() ?></h1>
		<?php the_content() ?>
	</section>

<?php
ct()->footer();
