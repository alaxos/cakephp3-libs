<?php 
use Cake\I18n\Time;
use Cake\I18n\I18n;
if(isset($entity))
{
	/**
	 * @var \Cake\I18n\Time
	 */
	$created  = isset($entity->created)  ? $entity->to_display_timezone('created')  : null;
	
	/**
	 * @var \Cake\I18n\Time
	 */
	$modified = isset($entity->modified) ? $entity->to_display_timezone('modified') : null;
	
	if(isset($created) && isset($modified) && $created->toDateTimeString() == $modified->toDateTimeString())
	{
		$modified = null;
	}
	
	/**
	 * @var \Cake\ORM\Entity
	 */
	$creator = isset($entity->creator) ? $entity->creator : null;
	
	/**
	 * @var \Cake\ORM\Entity
	 */
	$editor = isset($entity->editor) ? $entity->editor : null;
	
	$creator_name_field = isset($creator_name_field) ? $creator_name_field : null;
	$editor_name_field  = isset($editor_name_field)  ? $editor_name_field  : null;
	
	/*********************************************************************
	 * Dates display format
	 */
	$date_format   = null;
	$time_format   = null;
	
	$locale        = I18n::locale();
	$defaultLocale = isset($locale) ? $locale : 'en';
	$defaultLocale = strtolower($defaultLocale);
	$defaultLocale = str_replace('-', '_', $defaultLocale);
	
	switch($defaultLocale)
	{
		case 'fr':
		case 'fr_fr':
		case 'fr_ch':
		case 'fra':
		case 'fre':
			$date_format   = 'd.m.Y';
			$time_format   = 'H:i:s';
			break;
			
		default:
			$date_format   = 'Y-m-d';
			$time_format   = 'H:i:s';
			break;
	}
	
	/*********************************************************************
	 * Creator/Editor display names
	 */
	
	$username_properties = ['fullname', 'username', 'displayName', 'display_name', ['firstname', 'lastname']];
	
	if(isset($creator))
	{
		$creator_username_properties = $username_properties;
		$creator_name = null;
		if(isset($creator_name_field))
		{
			array_unshift($creator_username_properties, $creator_name_field);
		}
		
		foreach($creator_username_properties as $creator_username_property)
		{
			if(is_string($creator_username_property) && isset($creator->{$creator_username_property}))
			{
				$creator_name = $creator->{$creator_username_property};
				break;
			}
			elseif(is_array($creator_username_property))
			{
				$has_all_props = true;
				foreach($creator_username_property as $creator_username_prop)
				{
					if(!isset($creator->{$creator_username_prop}))
					{
						$has_all_props = false;
					}
				}
				
				if($has_all_props)
				{
					$creator_name = '';
					foreach($creator_username_property as $creator_username_prop)
					{
						$creator_name .= $creator->{$creator_username_prop} . ' ';
					}
					$creator_name = trim($creator_name);
				}
			}
		}
	}
	
	if(isset($editor))
	{
		$editor_username_properties = $username_properties;
		$editor_name = null;
		if(isset($editor_name_field))
		{
			array_unshift($editor_username_properties, $editor_name_field);
		}
	
		foreach($editor_username_properties as $editor_username_property)
		{
			if(is_string($editor_username_property) && isset($editor->{$editor_username_property}))
			{
				$editor_name = $editor->{$editor_username_property};
				break;
			}
			elseif(is_array($editor_username_property))
			{
				$has_all_props = true;
				foreach($editor_username_property as $editor_username_prop)
				{
					if(!isset($creator->{$editor_username_prop}))
					{
						$has_all_props = false;
					}
				}
				
				if($has_all_props)
				{
					$editor_name = '';
					foreach($editor_username_property as $editor_username_prop)
					{
						$editor_name .= $creator->{$editor_username_prop} . ' ';
					}
					$editor_name = trim($editor_name);
				}
			}
		}
	}
	
	/********************************************************************************************
	 * Print values
	 */
	
	if(isset($created))
	{
		if(isset($creator_name))
		{
			echo sprintf(__d('alaxos', 'created on %s at %s by %s'), $created->format($date_format), $created->format($time_format), $creator_name);
		}
		else
		{
			echo sprintf(__d('alaxos', 'created on %s at %s'), $created->format($date_format), $created->format($time_format));
		}
	}
	
	if(isset($modified))
	{
		if(isset($created))
		{
			echo ', ';
		}
		
		if(isset($editor_name))
		{
			echo sprintf(__d('alaxos', 'last update on %s at %s by %s'), $modified->format($date_format), $modified->format($time_format), $editor_name);
		}
		else
		{
			echo sprintf(__d('alaxos', 'last update on %s at %s'), $modified->format($date_format), $modified->format($time_format));
		}
	}
	
}
