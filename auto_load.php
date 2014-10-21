<?php
require_once(__DIR__."/simple_html_dom.php");
ini_set('date.timezone','Asia/Taipei');
class ClassAutoLoader {
        public function __construct() {
                spl_autoload_register(array($this,'loader'));
        }
        private function loader($className) {
                require $className . '.php';
        }
}
$autoload = new ClassAutoLoader();

function tryLock($lock_file)
{
	if(@symlink('/proc/'.getmypid(),$lock_file) !== FALSE)
		return true;
	if(is_link($lock_file) && !is_dir($lock_file))
	{
		unlink($lock_file);
		return tryLock($lock_file);
	}
	return false;
}

function CheckLock($filename)
{
	$lock_file = '/tmp/'.basename($filename).'.lock';
	if(!tryLock($lock_file))
		die(basename($filename).' is running'."\n");
	register_shutdown_function('unlink',$lock_file);
}

$ini_array = parse_ini_file(__DIR__."/db.ini",true);
if(!file_exists('./log/'))
	mkdir('./log/',0777);
$DB = $ini_array['DB'];
?>
