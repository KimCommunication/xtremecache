<?php

class FrontController extends FrontControllerCore {
    
    protected function smartyOutputContent($content)
	{
		$this->context->cookie->write();
		
		$html = '';
		
		if (is_array($content))
			foreach ($content as $tpl)
				$html .= $this->context->smarty->fetch($tpl, null, $this->getLayout());
		else
			$html = $this->context->smarty->fetch($content, null, $this->getLayout());

		$html = trim($html);

		if (!empty($html))
		{
                        // call own hook for xtreme cache to write content to cache
                        Hook::exec('actionRequestComplete', array(
                            'controller' => $this,
                            'output' => $html
                        ));
			echo $html;
		}
		else
			echo $html;
	}
}
