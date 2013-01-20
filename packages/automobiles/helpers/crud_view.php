<?php defined('C5_EXECUTE') or die(_("Access Denied."));

class CrudViewHelper {
	
	public function listTable(&$view, $records, $output_fields, $edit_action = 'edit', $delete_action = 'delete', $sort_action = null, $misc_actions = null, $id_field_name = 'id') {
		
		$out = array();
		
		$misc_actions = empty($misc_actions) ? array() : $misc_actions;
		
		//open wrappers
		if (!empty($sort_action)) {
			$out[] =  '<div class="sortable-container"'
		           . ' data-sortable-save-url="' . $view->action($sort_action) . '"'
		           . ' data-sortable-save-token="' . Loader::helper('validation/token')->generate() . '"'
		           . '>';
		}
		$out[] = '<table class="table table-striped">';
		
		//headings
		$out[] = '<thead>';
		$out[] = '<tr>';
		$out[] = empty($edit_action) ? '' : '<th>&nbsp;</th>';
		foreach ($misc_actions as $misc) {
			$out[] = ($misc['placement'] == 'left') ? '<th>&nbsp;</th>' : '';
		}
		foreach ($output_fields as $field => $label) {
			$out[] = "<th>{$label}</th>";
		}
		foreach ($misc_actions as $misc) {
			$out[] = ($misc['placement'] == 'right') ? '<th>&nbsp;</th>' : '';
		}
		$out[] = empty($sort_action) ? '' : '<th>&nbsp;</th>';
		$out[] = empty($delete_action) ? '' : '<th>&nbsp;</th>';
		$out[] = '</tr>';
		$out[] = '</thead>';
		
		//rows
		$out[] = '<tbody>';
		foreach ($records as $record) {
			$out[] = empty($sort_action) ? '<tr>' : '<tr data-sortable-id="' . $record[$id_field_name] . '">';
			$out[] = empty($edit_action) ? '' : '<td><a class="row-button" href="' . $view->action($edit_action, $record[$id_field_name]) . '" title="edit"><i class="icon-pencil"></i></a></td>';
			foreach ($misc_actions as $misc) {
				$out[] = ($misc['placement'] == 'left') ? '<td style="padding-left: 0;"><a class="row-button" href="' . $view->action($misc['action'], $record[$id_field_name]) . '" title="' . $misc['title'] . '"><i class="' . $misc['icon'] . '"></i></a></td>' : '';
			}
			$last_field = array_pop(array_keys($output_fields));
			foreach ($output_fields as $field => $label) {
				$out[] = ($field === $last_field) ?'<td class="last-field">' : '<td>';
				$out[] = htmlentities($record[$field]);
				$out[] = '</td>';
			}
			foreach ($misc_actions as $misc) {
				$out[] = ($misc['placement'] == 'right') ? '<td><a class="row-button" href="' . $view->action($misc['action'], $record[$id_field_name]) . '" title="' . $misc['title'] . '"><i class="' . $misc['icon'] . '"></i></a></td>' : '';
			}
			$out[] = empty($sort_action) ? '' : '<td><span class="row-button sortable-handle" title="drag to sort"><i class="icon-resize-vertical"></i></span></td>';
			$out[] = empty($delete_action) ? '' : '<td><a class="row-button" href="' . $view->action($delete_action, $record[$id_field_name]) . '" title="delete"><i class="icon-trash"></i></a></td>';
			$out[] = '</tr>';
		}
		$out[] = '</tbody>';
		
		//close wrappers
		$out[] = '</table>';
		$out[] = empty($sort_action) ? '' : '</div><!-- .sortable-container -->';
		
		//output
		$nonempty_lines = array();
		foreach ($out as $line) {
			if (!empty($line)) {
				$nonempty_lines[] = $line;
			}
		}
		return implode("\n", $nonempty_lines);
	}
	
}
