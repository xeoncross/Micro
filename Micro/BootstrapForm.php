<?php
/**
 *
 * Helper Class to output correct Twitter Bootstrap Markup.
 * Only option for now is the horizontal Form.
 *
 * Usage :
 *
 * 	$form = new BootstrapForm();
 * 	$form->input('Confirmation ID', 2345, "", array('type' => 'hidden'));
 * 	$form->input('Username', '', 'Please use alphanumeric characters');
 * 	$form->input('Email', '', "We won't spam you. we promise.", array('type' => 'email', 'placeholder' => 'youremail@example.com'));
 * 	$form->input('Password', '', '', array('type' => 'password'));
 * 	$form->input('Confirm Password', '', '', array('type' => 'password'));
 * 	$form->input('Profile Picture', '', '', array('name' => 'image', 'type' => 'file'));
 * 	$form->radio('Male', 'Male', '', array('name' => 'gender'));
 * 	$form->radio('Female', 'Female', '', array('name' => 'gender', 'checked' => 'checked'));
 * 	$form->textarea('Bio', '', 'Please tell us about yourself', array('rows' => 6));
 * 	$form->checkbox('Remember Me', '', '', array('checked' => true));
 * 	return $form->render('Submit', 'Cancel');
 */
namespace Micro;

class BootstrapForm
{
	public $inputs;

	// type, label, value, help, attributes, name
	public $templates = array(
		'input' => '<div class="form-group">
				<label for="%6$s">%2$s</label>
				<input %5$s>
				%4$s
			 </div>',
		'hidden' => '<input %5$s>',
		'checkbox' => '<div class="checkbox">
				<label>
					<input %5$s> %2$s
				</label>
				%4$s
			</div>',
		'radio' => '<div class="radio">
				<label>
					<input %5$s> %2$s
				</label>
				%4$s
			</div>',
		'textarea' => '<div class="form-group">
				<label for="%3$s">%2$s</label>
				<textarea %5$s>%3$s</textarea>
				%4$s
			 </div>',
		'select' => '<div class="form-group">
				<label for="%3$s">%2$s</label>
				<select %5$s>%3$s</select>
				%4$s
			 </div>',
  	);

 	public function input($label, $value = null, $help_text = null, array $attributes = array())
 	{
 		// Attributes: placeholder, disabled, readonly, etc...
 		$attributes += array(
 			'value' => $value,
 			'type' => 'text',
 			'class' => '',
 		);

 		if($attributes['type'] !== 'file') {
 			$attributes['class'] .= ' form-control';
 		}

 		$this->inputs[] = array(
 			'type' => $attributes['type'] === 'hidden' ? 'hidden' : 'input',
 			'label' => $label,
 			'value' => $value,
 			'text' => $help_text,
 			'attributes' => $attributes,
 		);
 	}

 	public function textarea($label, $value = null, $help_text = null, array $attributes = array())
 	{
 		// Attributes: placeholder, disabled, readonly, etc...
 		$attributes += array(
 			'class' => '',
 			'rows' => 4,
 		);

 		$attributes['class'] .= ' form-control';

 		$this->inputs[] = array(
 			'type' => 'textarea',
 			'label' => $label,
 			'value' => $value,
 			'text' => $help_text,
 			'attributes' => $attributes,
 		);
 	}

 	public function checkbox($label, $value = null, $help_text = null, array $attributes = array())
 	{
 		// Attributes: checked, disabled, readonly, etc...
 		$attributes += array(
 			'value' => $value,
 			'type' => 'checkbox',
 			'class' => '',
 		);

 		$this->inputs[] = array(
 			'type' => 'checkbox',
 			'label' => $label,
 			'value' => $value,
 			'text' => $help_text,
 			'attributes' => $attributes,
 		);
 	}

 	public function radio($label, $value = null, $help_text = null, array $attributes = array())
 	{
 		// Attributes: checked, disabled, readonly, etc...
 		$attributes += array(
 			'value' => $value,
 			'type' => 'radio',
 			'class' => '',
 		);

 		$this->inputs[] = array(
 			'type' => 'radio',
 			'label' => $label,
 			'value' => $value,
 			'text' => $help_text,
 			'attributes' => $attributes,
 		);
 	}

 	public function select($label, array $values, $value = null, $help_text = null, array $attributes = array())
 	{
 		// Attributes: multiple, disabled, readonly, etc...
 		$attributes += array(
 			'class' => '',
 		);

 		$attributes['class'] .= ' form-control';

 		$options = array();
 		foreach($values as $key => $option) {
 			$selected = ($option === $value ? 'selected="selected"' : '');
 			$options[] = '<option value="' . $key . '"' . $selected . '>' . $option . '</option>';
 		}

 		$this->inputs[] = array(
 			'type' => 'select',
 			'label' => $label,
 			'value' => join("\n", $option),
 			'text' => $help_text,
 			'attributes' => $attributes,
 		);
 	}

 	public function render($submit = NULL, $cancel = NULL, $reset = null)
 	{
		$h = function($v) {
			return htmlspecialchars($v, ENT_QUOTES, 'utf-8');
		};

 		$form = '';
 		foreach($this->inputs as $input) {

 			$input['text'] = $input['text'] ? '<p class="help-block">'. $input['text'] . '</p>' : '';
 			
 			// Base the name on the label if not given
 			if(empty($input['attributes']['name'])) {
				$input['attributes']['name'] = str_replace(' ', '_', strtolower($input['label']));
			}

			$input['name'] = $input['attributes']['name'];

 			if( ! in_array($input['type'], array('select', 'checkbox', 'radio'))) {
 				$input['value'] = $h($input['value']);
 			}

 			$attributes = array();
	 		foreach ($input['attributes'] as $key => $value) {
	 			$attributes[] = $h($key) . '="' . $h($value) . '"';
	 		}

	 		$input['attributes'] = join(" ", $attributes);

 			$form .= vsprintf($this->templates[$input['type']], $input) . "\n";
 		}

 		if($submit) {
 			$form .= '<button type="submit" class="btn btn-primary">' . $submit . '</button>';
 		}

 		if($reset) {
 			$form .= '<button type="reset" class="btn btn-warning reset">' . $reset . '</button>';
 		}

 		if($cancel) {
 			$form .= '<button type="button" class="btn btn-default cancel">' . $cancel . '</button>';
 		}

 		return $form;
 	}

 	public function __toString()
 	{
 		try {
 			return $this->render();
 		} catch(\Exception $e) {
 			return '' . $e;
 		}
 	}

}