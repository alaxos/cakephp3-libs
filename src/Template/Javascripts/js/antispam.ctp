<?php
if(isset($today_fieldname) && isset($form_dom_id))
{
    $hidden_field = $this->AlaxosForm->hidden($today_fieldname, ['value' => $today_fieldname]);
    
    $js   = [];
    $js[] = 'jQuery(document).ready(function(){';
    $js[] = '   jQuery("' . str_replace('"', '\"', $hidden_field) . '").prependTo(jQuery("#' . $form_dom_id . '"));';
    $js[] = '});';
    
    echo implode("\n", $js);
}