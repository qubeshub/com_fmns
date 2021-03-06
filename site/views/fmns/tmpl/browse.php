<?php 
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$this->css()
		 ->css('browse');
$this->js()
		 ->js('https://www.gstatic.com/charts/loader.js');

Document::setTitle(Lang::txt('COM_FMNS'));
Document::addScriptDeclaration($this->script);

Pathway::append(
	Lang::txt('COM_FMNS_BROWSE'),
	'index.php?option=' . $this->option . '&controller=' . $this->controller
);
?>

<main class="wrapper">
	<section class="browse_gantt">
	
		<h2 id="browse_gantt_heading">
			Browse FMNs
		</h2>
		
		<div id="browse_gantt_chart">
			<noscript>
				<p class="info">To view this page views graph, Javascript must be enabled.</p>
			</noscript>
		</div>
				
		<div id="browse_gantt_caption">
		</div>
  </section>
</main>
