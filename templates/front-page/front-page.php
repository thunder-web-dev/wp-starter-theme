<?php
/**
 * Шаблон главной страницы.
 */
ct()->header();
is_singular() && the_post();
?>

	<div class="front-page container">
		<h1>Шаблон главной страницы</h1>
	</div>

<?php
ct()->footer();
