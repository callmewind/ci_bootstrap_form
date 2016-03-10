<?php
defined('BASEPATH') OR exit('No direct script access allowed');

abstract class Field 
{
	public $name;
	protected $label;
	protected $initial;
	protected $rules;
	protected $CI;
	protected $post_value;


	protected function __construct($name, $label = '', $rules = array(), $initial = '')
	{
		$this->CI =& get_instance();
		$this->name = $name;
		$this->label = $label;
		$this->initial = $initial;
		$this->rules = Field::prep_rules($rules);
		$this->CI->form_validation->set_rules($name, $label, $rules);
		$this->post_value = $this->CI->input->post($this->name);
	}

	protected function prep_rules($rules) {
		return is_string($rules) ? preg_split('/\|(?![^\[]*\])/', $rules) : $rules;
	}

	protected function build_tag($name, $attributes = array(), $void = True, $content = '')
	{
		array_walk($attributes, function(&$value, $key){
			$value = $key.'="'.html_escape($value).'"';
		});
		$tag = '<'.$name.' '.implode(' ', $attributes).'>';
		if($content || !$void)
			$tag = $tag.$content.'</'.$name.'>';
		return $tag;
	}

	protected function is_post()
	{
		return $this->CI->input->server('REQUEST_METHOD') == 'POST';
	}

	protected function build_form_group($widget)
	{

		if($label = html_escape($this->label)) {

			if(in_array('required', $this->rules))
				$label .= '<span class="text-danger">*</span>';
				
			$label = '<label>'.$label.'</label>';
		}

		$error = form_error($this->name, '<span class="help-block">', '</span>');

		if(is_array($widget))
			$widget = implode($widget);


		$state_class = $error? ' has-error' : '';
		if(!$state_class && $this->is_post())
			$state_class = ' has-success';

		return $this->build_tag('div', array('class' => 'form-group'.$state_class), False, $label.$widget.$error);
	}
}


class Text_field extends Field
{
	function __construct($name, $label = '', $rules = array(), $initial = '')
	{
		parent::__construct($name, $label, $rules, $initial);
	}

	public function __toString()
	{
		$attributes = array(
			'name' => $this->name,
			'type' => 'text',
			'value' => $this->is_post()? $this->post_value : $this->initial,
			'class' => 'form-control'
		);

		if(in_array('required', $this->rules))
			$attributes['required'] = 'required';

		if(in_array('valid_email',  $this->rules))
			$attributes['type'] = 'email';

		if(in_array('valid_url',  $this->rules))
			$attributes['type'] = 'url';

		if(in_array('decimal',  $this->rules))
			$attributes['type'] = 'number';	
		return $this->build_form_group($this->build_tag('input', $attributes));
	}
}

class Textarea_field extends Field
{

	protected $rows;

	function __construct($name, $label = '', $rules = array(), $initial = '', $rows = 4)
	{
		parent::__construct($name, $label, $rules, $initial);
		$this->rows = $rows;
	}

	function __toString()
	{
		$attributes = array(
			'name' => $this->name,
			'class' => 'form-control'
		);
		if($this->rows)
			$attributes['rows'] = $this->rows;
		$contents =html_escape($this->is_post()? $this->post_value : $this->initial);
		return $this->build_form_group($this->build_tag('textarea', $attributes, False, $contents));
	}
}

abstract class Selection_inputs_field extends Field
{
	protected $choices;
	protected $type;

	function __construct($name, $type, $label = '', $rules = array(), $initial = array(), $choices = array())
	{
		parent::__construct($name, $label, $rules, $initial);
		$this->type = $type;
		$this->choices = $choices;
	}

	public function __toString()
	{
		$widgets = array();
		foreach($this->choices as $choice => $label) {
			$attributes = array(
				'name' => $this->name,
				'type' => $this->type,
				'value' => $choice
			);
			
			if ($this->is_post()){
				
				if (is_array($this->post_value) && in_array($choice, $this->post_value))
					$attributes['checked'] = 'checked';
				if ($this->post_value === $choice)
					$attributes['checked'] = 'checked';
			} else {
				if (is_array($this->initial) && in_array($choice, $this->initial))
					$attributes['checked'] = 'checked';
				if ($this->initial === $choice)
					$attributes['checked'] = 'checked';
			}
			
			$input = $this->build_tag('input', $attributes);

			$labelled_input = $this->build_tag('label', array(), FALSE, $input.$label);
			$widgets[] = $this->build_tag('div', array('class' => $this->type), FALSE, $labelled_input);
		}
		return $this->build_form_group(implode($widgets));
	} 

}

class Checkboxes_field extends Selection_inputs_field
{
	
	function __construct($name, $label = '', $rules = array(), $initial = array(), $choices = array())
	{
		parent::__construct($name, 'checkbox', $label, $rules, $initial, $choices);
	}
}

class Radios_field extends Selection_inputs_field
{
	
	function __construct($name, $label = '', $rules = array(), $initial = array(), $choices = array())
	{
		parent::__construct($name, 'radio', $label, $rules, $initial, $choices);
	}
}

class Bootstrap_form  {

	protected $fields = array();

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->library('form_validation');
	}

	public function add($field)
	{
		$this->fields[$field->name] = $field;
		return $this;
	}

	public function get($field)
	{
		return isset($this->fields[$field])? $this->fields[$field] : NULL;
	}

	public function __toString()
	{
		return implode($this->fields);
	}
}

function bootstrap_field($field)
{
	$CI =& get_instance();

	return $CI->bootstrap_form->get($field);
}



function bootstrap_form()
{
	$CI =& get_instance();

	return $CI->bootstrap_form;
}
