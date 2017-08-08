<?php
require_once('../boffice_config.php');

boffice_initialize();

echo boffice_template_simple("some title", "some body");