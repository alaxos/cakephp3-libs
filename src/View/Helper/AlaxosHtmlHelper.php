<?php
namespace Alaxos\View\Helper;

use Cake\View\Helper\HtmlHelper;

class AlaxosHtmlHelper extends HtmlHelper
{
	public function include_alaxos_js($block = true)
	{
		echo $this->script('Alaxos.alaxos/alaxos', ['block' => $block]);
	}
}