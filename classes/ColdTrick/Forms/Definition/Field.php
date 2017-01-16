<?php

namespace ColdTrick\Forms\Definition;

use ColdTrick\Forms\Exception\InvalidInputException;

class Field {
	
	/**
	 * @var array the field configiration
	 */
	protected $config;

	/**
	 * @var array the conditional section configuration
	 */
	protected $conditional_sections;
	
	/**
	 * @var \ColdTrick\Forms\Definition\ConditionalSection[] the confitional sections
	 */
	protected $conditional_sections_objects;
	
	/**
	 * @var mixed the submitted value of this field
	 */
	protected $value;
	
	/**
	 * Create a new form field
	 *
	 * @param array $config the field configiration
	 */
	public function __construct($config) {

		$this->config = $config;
		
		$this->conditional_sections = elgg_extract('conditional_sections', $this->config, []);
		unset($this->config['conditional_sections']);
		
		// set new name if missing
		if (!isset($this->config['name'])) {
			$this->config['name'] = '__field_' . (microtime(true) * 1000);
		}
	}
	
	/**
	 * Get the field type
	 *
	 * @return string
	 */
	public function getType() {
		return elgg_extract('#type', $this->config, '');
	}
	
	/**
	 * Get the name of the field
	 *
	 * @return string
	 */
	public function getName() {
		return elgg_extract('name', $this->config, '');
	}
	
	/**
	 * Get the input variables
	 *
	 * @return array
	 */
	public function getInputVars() {
		$result = $this->config;
		
		$options_key = 'options_values';
		$options = $this->getOptions();
		unset($result['options']);
		
		switch ($this->getType()) {
			case 'checkboxes':
				$options_key = 'options';
				break;
			case 'plaintext':
				$result['rows'] = 2;
				break;
		}
		
		if (!empty($options)) {
			$result[$options_key] = $options;
		}
		
		$result['pattern'] = $this->getPattern();
		unset($result['validation_rule']);
		
		$result['required'] = (bool) elgg_extract('required', $result, false);
		
		return $result;
	}
	
	/**
	 * Get the field configuration
	 *
	 * @return array
	 */
	public function getConfig() {
		return $this->config;
	}
	
	/**
	 * Get all the conditional sections for this field
	 *
	 * @return \ColdTrick\Forms\Definition\ConditionalSection[]
	 */
	public function getConditionalSections() {
		
		if (isset($this->conditional_sections_objects)) {
			return $this->conditional_sections_objects;
		}
		
		$this->conditional_sections_objects = [];
		foreach ($this->conditional_sections as $section) {
			$this->conditional_sections_objects[] = new ConditionalSection($section);
		}
		
		return $this->conditional_sections_objects;
	}
	
	/**
	 * Create the options for use in the inputVars
	 *
	 * @return array
	 */
	protected function getOptions() {
		$options = elgg_extract('options', $this->config, '');
		
		$result = explode(',', $options);
		$result = array_map('trim', $result);
		$result = array_filter($result);
		$result = array_unique($result);
		
		// values = keys
		$result = array_combine($result, $result);
		
		if ($this->getType() == 'select' && !elgg_extract('multiple', $this->config, false)) {
			$result = array_merge(['' => elgg_echo('forms:view:field:select:empty')], $result);
		}
		
		return $result;
	}
	
	/**
	 * Get a validation pattern to put on an input
	 *
	 * @return void|string
	 */
	protected function getPattern() {
		
		$rule = $this->getValidationRule();
		if (empty($rule)) {
			return;
		}
		
		if (!in_array($this->getType(), elgg_extract('input_types', $rule))) {
			return;
		}
		
		return elgg_extract('regex', $rule);
	}
	
	/**
	 * Get all the applied validation rules for this field
	 *
	 * @return array
	 */
	public function getValidationRules() {
		
		$result = [];
		
		$rule = $this->getValidationRule();
		if (!empty($rule)) {
			$result[elgg_extract('name', $rule)] = $rule;
		}
		
		foreach ($this->getConditionalSections() as $conditional_section) {
			$result = array_merge($result, $conditional_section->getValidationRules());
		}
		
		return $result;
	}
	
	/**
	 * Get the validation rule for this field
	 *
	 * @return fasle|array
	 */
	protected function getValidationRule() {
		
		$validation_rule = elgg_extract('validation_rule', $this->config);
		if (empty($validation_rule)) {
			return false;
		}
		
		$rule = forms_get_validation_rule($validation_rule);
		if (empty($rule)) {
			return false;
		}
		
		return $rule;
	}
	
	/**
	 * Fill the field from its input submitted value
	 *
	 * @return void
	 */
	public function populateFromInput() {
		
		foreach ($this->getConditionalSections() as $conditional_section) {
			$conditional_section->populateFromInput();
		}
		
		$this->setValue(get_input($this->getName()));
	}
	
	/**
	 * Set the value for this input field
	 *
	 * @param mixed $value the new value
	 *
	 * @return void
	 */
	public function setValue($value) {
		$this->value = $value;
	}
	
	/**
	 * Get the value of this field
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * Validate if the field value is valid according to the configuration
	 *
	 * @throws InvalidInputException
	 * @return void
	 */
	public function validate() {
		
		$this->validateRequired();
		
		$this->validatePattern();
	}
	
	/**
	 * Validate if the field is required
	 *
	 * @throws InvalidInputException
	 * @return void
	 */
	protected function validateRequired() {
		
		$required = (bool) elgg_extract('required', $this->config, false);
		if (!$required) {
			return;
		}
		
		if (isset($this->value) && ($this->value !== '')) {
			return;
		}
		
		throw new InvalidInputException(elgg_echo('forms:invalid_input_exception:required'));
	}
	
	/**
	 * Validate the regex pattern on the field
	 *
	 * @throws InvalidInputException
	 * @return void
	 */
	protected function validatePattern() {
		
		$pattern = $this->getPattern();
		if (empty($pattern)) {
			return;
		}
		
		if (preg_match('/' . $pattern . '/', $this->value)) {
			return;
		}
		
		throw new InvalidInputException(elgg_echo('forms:invalid_input_exception:pattern'));
	}
}
