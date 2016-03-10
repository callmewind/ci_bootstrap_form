# ci_bootstrap_form
Bootstrap forms for codeigniter. This is a work in progress.l

ci_bootstrap_form provides form definition, rendering and validation for codeigniter 3. Internally uses the form_validation library.

## Features

- Currently supported text fields, textareas, checkboxes and radiobuttons. More types coming soon.
- Partial HTML5 validation support (emails, numbers, required fields..)

## Installation

Simply put the Bootstrap_form.php file in your library folder. Include bootstrap css files in your view.

## Usage

Usage example:

### Controller.php:
```
$this->load->library('bootstrap_form');

$this->bootstrap_form->
			add(new Text_field('member[name]', _('Name'), 'trim|required|max_length[40]'))->
			add(new Text_field('member[email]', _('Email'), 'trim|required|valid_email|max_length[255]'))->
			add(new Text_field('member[phone]', _('Phone'), 'trim|required|max_length[20]|min_length[7]'))->
			add(new Text_field('project[name]', _('Project name'), 'trim|required|max_length[40]'))->
			add(new Radios_field('project[finished]', _('Is the project finished?'), 'trim|required', NULL, array('1' => _('Yes'), '' => _('No'))))->
			add(new Textarea_field('project[description]', _('Project description'), 'trim|required|max_length[1000]'));
```

### View.php
```
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<body>
<div class="container">
<div class="col-md-12">
	<?= bootstrap_form() ?>
</div>
</div>
</body>
</html>
```
Alternatively you can use bootstrap_field() for rendering each form separately.
