<?php

/**
 * CSS-JS-BOOSTER
 * 
 * An easy to use PHP-Library that combines, optimizes, dataURI-fies, re-splits, 
 * compresses and caches your CSS and JS for quicker loading times.
 * 
 * PHP version 5
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published 
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.
 * If not, see <http://www.gnu.org/licenses/lgpl-3.0.txt>
 * 
 * @category  PHP 
 * @package   CSS-JS-Booster 
 * @author    Christian Schepp Schaefer <schaepp@gmx.de> <http://twitter.com/derSchepp>
 * @copyright 2009 Christian Schepp Schaefer
 * @license   http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 3.0
 * @link      http://github.com/Schepp/CSS-JS-Booster 
 */

// Starting zlib-compressed output
@ini_set('zlib.output_compression',2048);
@ini_set('zlib.output_compression_level',4);

// Turning on strict error reporting
@ini_set("display_errors", 1);
@error_reporting(E_ALL);

// Starting gzip-compressed output if zlib-compression is turned off
if (
isset($_SERVER['HTTP_ACCEPT_ENCODING']) 
&& substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') 
&& function_exists('ob_gzhandler') 
&& !ini_get('zlib.output_compression')
) @ob_start('ob_gzhandler');
else @ob_start();

/**
 * Inclusion of user agent detection class
 */
include_once('browser_class_inc.php');

/**
 * Inclusion of 
 */
include_once('csstidy-1.3/class.csstidy.php');

/**
 * CSS-JS-BOOSTER
 * 
 * An easy to use PHP-Library that combines, optimizes, dataURI-fies, re-splits, 
 * compresses and caches your CSS and JS for quicker loading times.
 * 
 * @category  PHP 
 * @package   CSS-JS-Booster 
 * @author    Christian Schepp Schaefer <schaepp@gmx.de> <http://twitter.com/derSchepp>
 * @copyright 2009 Christian Schepp Schaefer
 * @license   http://www.gnu.org/copyleft/lesser.html The GNU LESSER GENERAL PUBLIC LICENSE, Version 3.0
 * @link      http://github.com/Schepp/CSS-JS-Booster 
 */
class Booster {

    /**
     * Defines source to take the CSS stylesheets from.
     * 
     * It accepts foldernames, filenames, multiple files and folders comma-delimited in strings or as array.
     * When passing foldernames, containing files will be processed in alphabetical order.
     * The variable also accepts a stylesheet-string.
     * Defaults to "css".
     * @var    mixed
     * @access public 
     * @see    $css_stringmode
     */
	public $css_source = 'css';

    /**
     * Defines media-attribute for CSS markup output
     *
     * Specify differing media-types like "print", "handheld", etc.
     * Defaults to "all".
     * @var    string 
     * @access public 
     */
	public $css_media = 'all';

    /**
     * Defines rel-attribute for CSS markup output
     *
     * Specify differing relations like "alternate stylesheet"
     * Defaults to "stylesheet".
     * @var    string 
     * @access public 
     */
	public $css_rel = 'stylesheet';

    /**
     * Defines a title-attribute for CSS markup output
     *
     * If you like to title multiple stylesheets
     * Defaults to "Standard".
     * @var    string 
     * @access public 
     */
	public $css_title = 'Standard';

    /**
     * Defines the markup language to use.
     *
     * Defaults to "XHTML".
     * @var    string 
     * @access public 
     */
	public $css_markuptype = 'XHTML';

    /**
     * Defines in how many parts the CSS output shall be split
     *
     * As newer browsers support more than 2 concurrent parallel connections 
     * to a webserver you can decrease loading-time by splitting the output up 
     * into more than one file.
     * Defaults to "2".
     * @var    number 
     * @access public 
     */
	public $css_totalparts = 2;

    /**
     * Defines which part to ouput when retrieving CSS in multiple parts
     *
     * Used by accompagning script "booster_css.php"
     * Defaults to "0".
     * @var    number 
     * @access public 
     */
	public $css_part = 0;

    /**
     * Defines if source-file retrieval shall be recursive
     *
     * Only matters when passing folders as source-parameter.
     * If set to "TRUE" contents of folders found inside source-folder are also fetched.
     * Defaults to "FALSE".
     * @var    boolean 
     * @access public  
     */
	public $css_recursive = FALSE;

    /**
     * Switches on string-mode, when passing styleheet-strings as source
     *
     * Instead of folders and files to read and parse you can also pass
     * stylesheet-code as source. But, this only works if you switch string-mode on.
     * Defaults to "FALSE".
     * @var    boolean 
     * @access public  
     * @see    $css_source
     */
	public $css_stringmode = FALSE;

    /**
     * Defines the base-folder for all files referenced in stylesheet-string
     *
     * When being in string-mode, the booster prepends this path going out from the caller-location 
     * in order to find all referenced files.
     * Defaults to "./".
     * @var    string 
     * @access public 
     * @see    $css_stringmode
     */
	public $css_stringbase = './';

    /**
     * Used to store the date of last change of a stylesheet-string
     *
     * Is set to the file-time of the calling script during construction.
     * @var    integer 
     * @access private  
     * @see    $css_stringmode
     */
	private $css_stringtime = 0;

    /**
     * Defines source to take the JS from.
     * 
     * It accepts foldernames, filenames, multiple files and folders comma-delimited in strings or as array.
     * When passing foldernames, containing files will be processed in alphabetical order.
     * Defaults to "js".
     * @var    mixed 
     * @access public 
     */
	public $js_source = 'js';

    /**
     * Defines in how many parts the JS output shall be split
     *
     * Newer browsers support more than 2 concurrent parallel connections 
     * but NOT for JS-files. So here one single output-file would be best. 
     * You can still uppen the number of output-files here if like.
     * Defaults to "1".
     * @var    integer 
     * @access public  
     */
	public $js_totalparts = 1;

    /**
     * Defines which part to ouput when retrieving JS in multiple parts
     *
     * Used by accompagning script "booster_js.php"
     * Defaults to "0".
     * @var    integer 
     * @access public  
     */
	public $js_part = 0;

    /**
     * Defines if source-file retrieval shall be recursive
     *
     * Only matters when passing folders as source-parameter.
     * If set to "TRUE" contents of folders found inside source-folder are also fetched.
     * Defaults to "FALSE".
     * @var    boolean 
     * @access public  
     */
	public $js_recursive = FALSE;

    /**
     * Switches on string-mode, when passing javascript-strings as source
     *
     * Instead of folders and files to read and parse you can also pass
     * javascript-code as source. But, this only works if you switch string-mode on.
     * Defaults to "FALSE".
     * @var    boolean 
     * @access public  
     * @see    $css_source
     */
	public $js_stringmode = FALSE;

    /**
     * Defines the directory to use for caching
     *
     * The directory is relative to "booster"-folder and should be write-enabled
     * Defaults to "booster_cache".
     * @var    string 
     * @access public 
     */
	public $booster_cachedir = 'booster_cache';

    /**
     * Used to remember if the working-path has already been calculated.
     * @var    boolean 
     * @access private 
      * @see    setcachedir
    */
	private $booster_cachedir_transformed = FALSE;

    /**
     * Used to to store user-agent info
     * @var    object 
     * @access public 
     * @see    __construct
     */
	public $browser;

    /**
     * Switch debug mode on/off
     * @var    boolean 
     * @access public  
     */
	public $debug = FALSE;
    
    /**
     * Constructor
     * 
     * Sets @var $css_stringtime to caller file time
     * Invokes new browser object for further use
     * 
     * @return void   
     * @access public 
     */
    public function __construct()
    {
		$this->css_stringtime = filemtime(realpath($_SERVER['SCRIPT_FILENAME']));
		$this->browser = new browser();
    }

    /**
     * Setcachedir calculates correct cache-path once and checks directory's writability
     * 
     * @return void   
     * @access public 
     */
	public function setcachedir()
	{
		// Check if @var booster_cachedir_transformed is still FALSE
		if(!$this->booster_cachedir_transformed) 
		{
			$this->booster_cachedir = str_replace('\\','/',dirname(__FILE__)).'/'.$this->booster_cachedir;
			$this->booster_cachedir_transformed = TRUE;
		}
		// Throw a warning and quit if cache-directory doesn't exist or isn't writable
		if(!@is_dir($this->booster_cachedir) && !@mkdir($this->booster_cachedir,0777)) 
		{
			echo "/* You need to create a directory \"".$this->booster_cachedir."\" with CHMOD 0777 rights!!! */\r\n\r\n";
			echo "body *:before {content: \"You need to create a directory ".$this->booster_cachedir." with CHMOD 0777 rights!!!\";}\r\n\r\n";
			exit;
		}
	}

    /**
     * Getpath calculates the relative path between @var $path1 and @var $path2
     * 
     * @param  string    $path1
     * @param  string    $path2
     * @param  string    $path1_sep   Sets the folder-delimiter, defaults to '/'
     * @return string    relative path between @var $path1 and @var $path2
     * @access protected 
     */
	protected function getpath($path1 = '',$path2 = '',$path1_sep = '/')
	{
		$path2 = explode($path1_sep, $path2);
		$path1 = explode($path1_sep, $path1);
		$path = '.';
		$fix = '';
		$diff = 0;
		for($i = -1; ++$i < max(($rC = count($path2)), ($dC = count($path1)));)
		{
			if(isset($path2[$i]) and isset($path1[$i]))
			{
				if($diff)
				{
					$path .= $path1_sep.'..';
					$fix .= $path1_sep.$path1[$i];
					continue;
				}
				if($path2[$i] != $path1[$i])
				{
					$diff = 1;
					$path .= $path1_sep.'..';
					$fix .= $path1_sep.$path1[$i];
					continue;
				}
			}
			elseif(!isset($path2[$i]) and isset($path1[$i]))
			{
				for($j = $i-1; ++$j < $dC;)
				{
					$fix .= $path1_sep.$path1[$j];
				}
				break;
			}
			elseif(isset($path2[$i]) and !isset($path1[$i]))
			{
				for($j = $i-1; ++$j < $rC;)
				{
					$fix = $path1_sep.'..'.$fix;
				}
				break;
			}
		}
		return $path.$fix;
	} 

    /**
     * Getfiles returns all files of a certain type within a folder
     * 
     * @param  string    $source    folder to look for files
     * @param  string    $type      sets file-type/suffix (for security reasons)
     * @param  boolean   $recursive tells the script to scan all subfolders, too
     * @param  array     $files     prepopulated array of files to append to and return
     * @return array     filenames sorted alphabetically
     * @access protected 
     */
	protected function getfiles($source = '',$type = '',$recursive = FALSE,$files = array())
	{
		// Remove any trailing slash
		$source = rtrim($source,'/');
		// Check if @var $source really is a folder
		if(is_dir($source))
		{
			$handle=opendir($source);
			while(false !== ($file = readdir($handle)))
			{
				if($file[0] != '.')
				{
					// If it is a folder
					if(is_dir($source.'/'.$file)) 
					{
						 // If the @var $recursive is set to "TRUE" start fetching the subfolder
						if($recursive) $files = $this->getfiles($source.'/'.$file,$type,$recursive,$files);
					}
					// If it is a file and if the filetype matches
					else if(substr($file,strlen($file) - strlen($type), strlen($type)) == $type) 
					{
						// Add to file-list
						array_push($files,$source.'/'.$file);
					}
				}
			}
			closedir($handle);
			// Sort list alphabetically
			array_multisort($files, SORT_ASC, $files);
		}
		// If @var $source is a file, add it to the file-list
		elseif(is_file($source) && substr($source,strlen($source) - strlen($type), strlen($type)) == $type) array_push($files,$source);
		// Return file-list
		return $files;
	}

    /**
     * Getfilestime returns the timestamp of the newest file of a certain type within a folder
     * 
     * @param  mixed   $source    single folder or multiple comma-delimited folders or array of folders in which to look for files
     * @param  string  $type      sets file-type/suffix (for security reasons)
     * @param  boolean $recursive tells the script to scan all subfolders, too
     * @param  integer $filestime prepopulated timestamp to also check against
     * @return integer timestamp of the newest of all scanned files
     * @access public  
     */
	public function getfilestime($source = '',$type = '',$recursive = FALSE,$filestime = 0)
	{
		// Load @var $source with an array made form @var $source parameter
		if(is_array($source)) $source = $source;
		else $sources = explode(',',$source);

		reset($sources);
		for($i=0;$i<sizeof($sources);$i++)
		{
			$source = current($sources);
			 // Remove any trailing slash
			$source = rtrim($source,'/');
			
			// Check if @var $source really is a folder
			if(is_dir($source))
			{
				// Get a list (array) of all folders and files inside that folder
				$files = $this->getfiles($source,$type,$recursive);
				// Check all list-item's timestamps
				for($i=0;$i<count($files);$i++) 
				{
					// In case it is a folder, run this funtion on the folder
					if(is_dir($files[$i])) 
					{
						if($recursive) $filestime = $this->getfilestime($files[$i],$type,$recursive,$filestime);
					}
					// In case it is a file, get its timestamp
					if(is_file($files[$i])) 
					{
						if(filemtime($files[$i]) > $filestime) $filestime = filemtime($files[$i]);
					}
				}
			}
			// If @var $source is a file check its file time
			elseif(is_file($source) && filemtime($source) > $filestime) $filestime = filemtime($source);
			next($sources);
		}
		// Return most recent timestamp
		return $filestime;
	}

    /**
     * Getfilescontents puts together all contents from files of a certain type within a folder
     * 
     * @param  string    $source       folder to look for files or file or code-string
     * @param  string    $type         sets file-type/suffix (for security reasons)
     * @param  boolean   $recursive    tells the script to scan all subfolders, too
     * @param  string    $filescontent prepopulated string to append to and return
     * @return string    Return all file contents
     * @access protected 
     */
	protected function getfilescontents($source = '',$type = '',$recursive = FALSE,$filescontent = '')
	{
		// Remove any trailing slash
		$source = rtrim($source,'/');
		
		// Prepare content storage
		$currentfilecontent = '';
		
		// If @var $source is a folder, get file-list and call itself on them
		if(is_dir($source))
		{
			$files = $this->getfiles($source,$type,$recursive);
			for($i=0;$i<count($files);$i++) $filescontent .= $this->getfilescontents($files[$i],$type,$recursive);
		}
		// If @var $source is a file
		elseif(is_file($source))
		{
			if($this->debug) $filescontent .= "/* file: ".$source." */\r\n";
			$currentfilecontent = file_get_contents($source);
		}
		// If @var $source is a string
		else $currentfilecontent = $source;

		// Find and resolve import-rules
		preg_match_all('/@import(\s|url\()[\'"]*(.+\.)[\'"]*\)*([^;]*);/',$currentfilecontent,$treffer,PREG_PATTERN_ORDER);
		if($this->debug) echo "/* import-rule-findings: ".count($treffer[0])." */\r\n";
		for($i=0;$i<count($treffer[0]);$i++)
		{
			$importfile = dirname($source).'/'.$treffer[2][$i];
			if(trim($treffer[3][$i]) != '') $mediatype = trim($treffer[3][$i]);
			else $mediatype = 'all';
			if($this->debug) $filescontent .= "/* importfile: ".$importfile." */\r\n";
			if(file_exists($importfile)) $currentfilecontent = str_replace($treffer[0][$i],"@media ".$mediatype." {\r\n".getfilescontents($importfile,$type)."}\r\n",$currentfilecontent);
		}
		
		// Append to @var $filescontent
		$filescontent .= $currentfilecontent;

		// @todo Delete as it is not needed any longer at this point
		// if(strlen($filescontent)) file_put_contents($this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',$source).'_'.$type.'_cache.txt',$filescontent);

		return $filescontent;
	}

    /**
     * Css_tidy calls the external library CSS Tidy in order to optimize the stylesheet
     * 
     * @param  string    styles-string
     * @return string    optimized styles-string
     * @access protected 
     */
	protected function css_tidy($filescontent = '')
	{
		$css = new csstidy();
		$css->set_cfg('sort_selectors',false);
		$css->set_cfg('sort_properties',false);
		$css->set_cfg('merge_selectors',0);
		$css->set_cfg('optimise_shorthands',1);
		$css->set_cfg('compress_colors',true);
		$css->set_cfg('compress_font-weight',true);
		$css->set_cfg('lowercase_s',false);
		$css->set_cfg('case_properties',1);
		$css->set_cfg('remove_bslash',false);
		$css->set_cfg('remove_last_;',true);
		$css->set_cfg('discard_invalid_properties',false);
		$css->load_template('high_compression');
		$result = $css->parse($filescontent);
		$filescontent = $css->print->plain();
		return $filescontent;
	}
	
    /**
     * Css_datauri embeds external files like images into the stylesheet
     * 
     * Depending on the browser and operating system, this funtion does the following:
     * IE 6 and 7 on XP and IE 7 on Vista or higher don't understand data-URIs, but a proprietary format named MHTML. 
     * So they get served that.
     * Any other common browser understands data-URIs, even IE 8 up to a file-size of 24KB, so those get data-URI-embedding
     * IE 6 on Vista or higher doesn't understand any of the embeddings so it just gets standard styles.
     * 
     * @param  integer   $filestime    timestamp of the last modification of the content following
     * @param  string    $filescontent stylesheet-content
     * @return string    stylesheet-content with data-URI or MHTML embeddings
     * @see    function  Setcachedir
     * @access protected 
     */
	protected function css_datauri($filestime = 0,$filescontent = '')
	{
		// Call Setcachedir to make sure, cache-path has been calculated
		$this->setcachedir();
		
		// Prepare different RegExes
		// Media-files (currently images and fonts)
		$regex_embed = '/url\([\'"]*(.+?\.)(gif|png|jpg|eot|otf|svg|ttf)[\'"]*\)/msi';
		// Any files
		$regex_url = '/url\([\'"]*(.+?\..+?)[\'"]*\)/msi';

		// Prepare @var $dir that we need to prepend as path to any images we find to get the full path
		// if @var $css_source is a folder
		if(is_dir($this->css_source)) $dir = $this->css_source;
		// if @var $css_source is a file
		elseif(is_file($this->css_source)) $dir = dirname($this->css_source);
		// if @var $css_source is code-string
		else $dir = rtrim($this->getpath(dirname($_SERVER['SCRIPT_FILENAME']).'/'.$this->css_stringbase,str_replace('\\','/',dirname(__FILE__))),'/');
		
		// --------------------------------------------------------------------------------------

		// If User Agent is IE 6/7 on XP or IE 7 on Vista or higher proceed with MHTML-embedding
		if(
			$this->browser->family == 'MSIE' && $this->browser->platform == 'Windows' && 
			(
				(round(floatval($this->browser->familyversion)) == 6 && floatval($this->browser->platformversion) < 6) || 
				(round(floatval($this->browser->familyversion)) == 7 && floatval($this->browser->platformversion) >= 6)
			)
		)
		{
			// The @var $mhtmlarray collects references to all processed images so that we can look up if we already have embedded a certain image
			$mhtmlarray = array();
		
			// If we are in normal mode use filename of the sourcefile as cache filename
			if(!$this->css_stringmode) 
			{
				// identifier for the cache-files
				$identifier = preg_replace('/[^a-z0-9,\-_]/i','',$this->css_source);
				// The external absolute path to where "booster_mhtml.php" resides
				$referrer_parsed = parse_url(dirname($_SERVER['REQUEST_URI']));
				$mhtmlpath = dirname($referrer_parsed['path']);
			}
			// If we are in string mode (which means no available filenames) do an md5 of the contents as cache filename
			else 
			{
				// identifier for the cache-files
				$identifier = md5($this->css_source);
				// The external absolute path to where "booster_mhtml.php" resides
				$mhtmlpath = '/'.$this->getpath(str_replace('\\','/',dirname(__FILE__)),rtrim($_SERVER['DOCUMENT_ROOT'],'/'));
			}
			
			// Cachefile for the styles
			$cachefile = $this->booster_cachedir.'/'.$identifier.'_datauri_ie_cache.txt';
			// Cachefile for the extra MHTML-data
			$mhtmlfile = $this->booster_cachedir.'/'.$identifier.'_datauri_mhtml_cache.txt';
			
			
			
			// Check if cachefile already exists and if it is newer than the timestamp given
			if(!file_exists($cachefile) || $filestime > filemtime($cachefile))
			{
				// If not, start putting together the styles and MHTML
				$mhtmlcontent = "Content-Type: multipart/related; boundary=\"_ANY_STRING_WILL_DO_AS_A_SEPARATOR\"\r\n\r\n";
	
				preg_match_all($regex_embed,$filescontent,$treffer,PREG_PATTERN_ORDER);
				for($i=0;$i<count($treffer[0]);$i++)
				{
					// Calculate full image path
					$imagefile = str_replace('\\','/',dirname(__FILE__)).'/'.$dir.'/'.$treffer[1][$i].$treffer[2][$i];
					// Create a new anchor-tag for the MHTML-file
					$imagetag = 'img'.$i;
					
					// If image-file exists and if file-size is lower than 24 KB
					if(file_exists($imagefile) && filesize($imagefile) < 24000) 
					{
						// Replace reference to image with reference to MHTML-file with corresponding anchor
						$filescontent = str_replace($treffer[0][$i],'url(mhtml:http://'.$_SERVER['HTTP_HOST'].$mhtmlpath.'/booster_mhtml.php?dir='.$identifier.'!'.$imagetag.')',$filescontent);
	
						// Look up in our list if we did not already process that exact file, if not append it
						if(!isset($mhtmlarray[$imagetag])) 
						{
							$mhtmlcontent .= "--_ANY_STRING_WILL_DO_AS_A_SEPARATOR\r\n";
							$mhtmlcontent .= "Content-Location:".$imagetag."\r\n";
							$mhtmlcontent .= "Content-Transfer-Encoding:base64\r\n\r\n";
							$mhtmlcontent .= base64_encode(file_get_contents($imagefile))."==\r\n";
							
							// Put file on our processed-list
							$mhtmlarray[$imagetag] = 1;
						}
					}
				}
				$mhtmlcontent .= "\r\n\r\n";
		
				// Hack suggested by Stoyan Stafanov: prepend a star in front of background-property
				$filescontent = preg_replace('/(background[^;]+?mhtml)/','*$1',$filescontent);
				
				// Scan for any left file-references and adjust their path
				preg_match_all($regex_url,$filescontent,$treffer,PREG_PATTERN_ORDER);
				for($i=0;$i<count($treffer[0]);$i++)
				{
					if(substr(str_replace(array('"',"'"),'',$treffer[1][$i]),0,5) != 'data:' && substr(str_replace(array('"',"'"),'',$treffer[1][$i]),0,6) != 'mhtml:') $filescontent = str_replace('url('.$treffer[1][$i].')','url('.$dir.'/'.$treffer[1][$i].')',$filescontent);
				}
				
				// Store the cache-files
				@file_put_contents($cachefile,$filescontent);
				@chmod($cachefile,0777);
				@file_put_contents($mhtmlfile,$mhtmlcontent);
				@chmod($mhtmlfile,0777);
			}
			// Cache-file exists
			else $filescontent = file_get_contents($cachefile);
		}
		
		// --------------------------------------------------------------------------------------

	
		// If IE 6 browser on Vista or higher (like IETester under Vista / Windows 7 for example) do not embed
		elseif(
			$this->browser->family == 'MSIE' && floatval($this->browser->familyversion) < 7 && 
			$this->browser->platform == 'Windows' && floatval($this->browser->platformversion) >= 6
		)
		{
			// Scan for any file-references and adjust their path
			preg_match_all($regex_url,$filescontent,$treffer,PREG_PATTERN_ORDER);
			for($i=0;$i<count($treffer[0]);$i++)
			{
				if(substr(str_replace(array('"',"'"),'',$treffer[1][$i]),0,5) != 'data:' && substr(str_replace(array('"',"'"),'',$treffer[1][$i]),0,6) != 'mhtml:') $filescontent = str_replace('url('.$treffer[1][$i].')','url('.$dir.'/'.$treffer[1][$i].')',$filescontent);
			}
		}
		
		// --------------------------------------------------------------------------------------

		// If any other and (then we assume) data-URI-compatible browser
		else
		{
			if($this->debug) $filescontent .= "/* lastmodified: ".intval($this->css_stringtime)." / ".date("d.m.Y H:i:s",$this->css_stringtime)." */\r\n";
			if($this->debug) $filescontent .= "/* dir: ".$dir." */\r\n";
		
			// If we are in normal mode use filename of the sourcefile as cache filename
			if(!$this->css_stringmode) $cachefile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',$this->css_source).'_datauri_cache.txt';
			// If we are in string mode (which means no available filenames) do an md5 of the contents as cache filename
			else $cachefile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',md5($this->css_source)).'_datauri_cache.txt';
			
			
			
			// Check if cachefile already exists and if it is newer than the timestamp given
			if(!file_exists($cachefile) || $filestime > filemtime($cachefile))
			{
				preg_match_all($regex_embed,$filescontent,$treffer,PREG_PATTERN_ORDER);
				if($this->debug) echo "/* image-findings: ".count($treffer[0])." */\r\n";
				for($i=0;$i<count($treffer[0]);$i++)
				{
					// Calculate full image path
					$imagefile = str_replace('\\','/',dirname(__FILE__)).'/'.$dir.'/'.$treffer[1][$i].$treffer[2][$i];
					if($this->debug) echo "/* imagefile: ".$imagefile." */\r\n";
					
					// If image-file exists and if file-size is lower than 24 KB
					if(file_exists($imagefile) && filesize($imagefile) < 24000) $filescontent = str_replace($treffer[0][$i],'url(data:image/'.$treffer[2][$i].';base64,'.base64_encode(file_get_contents($imagefile)).')',$filescontent);
				}

				// Scan for any left file-references and adjust their path
				preg_match_all($regex_url,$filescontent,$treffer,PREG_PATTERN_ORDER);
				for($i=0;$i<count($treffer[0]);$i++)
				{
					if(substr(str_replace(array('"',"'"),'',$treffer[1][$i]),0,5) != 'data:' && substr(str_replace(array('"',"'"),'',$treffer[1][$i]),0,6) != 'mhtml:') $filescontent = str_replace('url('.$treffer[1][$i].')','url('.$dir.'/'.$treffer[1][$i].')',$filescontent);
				}
				
				// Store the cache-file
				@file_put_contents($cachefile,$filescontent);
				@chmod($cachefile,0777);
			}
			// Cache-file exists
			else if(file_exists($cachefile)) $filescontent = file_get_contents($cachefile);
		}
		
		// --------------------------------------------------------------------------------------

		return $filescontent;
	}

    /**
     * Css_split takes a multiline CSS-string and splits it according to @var $css_totalparts and @var $css_part
     * 
     * @param  string    $filescontent contents to split
     * @return string    requested part-number of splitted content
     * @access protected 
     */
	protected function css_split($filescontent = '')
	{
		// If sum of parts is 1 or requested part-number is 0 return full string
		if($this->css_totalparts == 1 || $this->css_part == 0 || $this->css_stringmode) return $filescontent;
		// Else process string
		else
		{
			// Split at every new line
			$filescontentlines = explode("\n",$filescontent);
			// Prepare storage for parts
			$filescontentparts = array();
			$i = 0;
			// Create all parts
			// @todo could maybe be cached, too?
			for($j=0;$j<intval($this->css_totalparts);$j++)
			{
				$filescontentparts[$j] = '';
				while(strlen($filescontentparts[$j]) < ceil(strlen($filescontent) / $this->css_totalparts) && isset($filescontentlines[$i]))
				{
					$filescontentparts[$j] .= $filescontentlines[$i]."\n";
					$i++;
				}
			}
			// Return only the requested part
			return $filescontentparts[$this->css_part - 1];
		}
	}
		
    /**
     * Css fetches and optimizes all stylesheet-files
     * 
     * @return string optimized stylesheet-code
     * @access public 
     */
	public function css()
	{
		// Call Setcachedir to make sure, cache-path has been calculated
		$this->setcachedir();
		
		// Empty storage for stylesheet-contents to come
		$filescontent = '';
		// Specify file extension "css" for security reasons
		$type = 'css';
	
		// Prepare @var $sources as an array
		// if @var $css_source is an array
		if(is_array($this->css_source)) $sources = $this->css_source;
		// if @var $css_source is not an array and @var $css_stringmode is not set
		elseif(!$this->css_stringmode) $sources = explode(',',$this->css_source);
		// if @var $css_stringmode is set
		else $sources = array($this->css_source);
		
		reset($sources);
		for($i=0;$i<sizeof($sources);$i++)
		{
			$source = current($sources);
			// Remove any trailing slash
			$source = rtrim($source,'/');
			if($source != '')
			{
				// If current source is a folder or file, get its most recent filetime
				if(is_dir($source) || is_file($source)) $filestime = $this->getfilestime($source,$type,$this->css_recursive);
				// If current source is a string read the filetime from @var $css_stringtime
				else $filestime = $this->css_stringtime;
				
			
				// Defining the cache-filename
					// If IE 6/7 on XP or IE 7 on Vista/Win7
					if(
						$this->browser->family == 'MSIE' && $this->browser->platform == 'Windows' && 
						(
							(round(floatval($this->browser->familyversion)) == 6 && floatval($this->browser->platformversion) < 6) || 
							(round(floatval($this->browser->familyversion)) == 7 && floatval($this->browser->platformversion) >= 6)
						)
					)
					{
						// If we are in normal mode use filename of the sourcefile as cache filename
						if(!$this->css_stringmode) $cachefile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',$source).'_datauri_ie_cache.txt';
						// If we are in string mode (which means no available filenames) do an md5 of the contents as cache filename
						else $cachefile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',md5($source)).'_datauri_ie_cache.txt';
					}
					
					
					// If IE 6 browser on Vista or higher (like IETester under Windows 7 for example), skip cache
					elseif(
						$this->browser->family == 'MSIE' && floatval($this->browser->familyversion) < 7 && 
						$this->browser->platform == 'Windows' && floatval($this->browser->platformversion) >= 6
					)
					{
						// No cache file
						$cachefile = '';
					}
					
					
					// If any other and (then we assume) data-URI-compatible browser
					else 
					{
						// If we are in normal mode use filename of the sourcefile as cache filename
						if(!$this->css_stringmode) $cachefile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',$source).'_datauri_cache.txt';
						// If we are in string mode (which means no available filenames) do an md5 of the contents as cache filename
						else $cachefile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',md5($source)).'_datauri_cache.txt';
					}
				
				
				// If that cache-file is there, fetch its contents
				if(file_exists($cachefile) && filemtime($cachefile) >= $filestime) $filescontent .= file_get_contents($cachefile);
				// if that cache-file does not exist, create it
				else
				{
					// If current source is a folder or file, get its contents
					if(is_dir($source) || is_file($source)) $currentfilescontent = $this->getfilescontents($source,$type,$this->css_recursive);
					// If current source is already a string
					else $currentfilescontent = $source;
					
					// Optimize stylesheets with CSS Tidy
					$currentfilescontent = $this->css_tidy($currentfilescontent);

					// Embed media to save HTTP-requests
					$filescontent .= $this->css_datauri($filestime,$currentfilescontent);
				}
				$filescontent .= "\n";
			}
			next($sources);
		}
		// Split results up in order to have multiple parts load in parallel and get the currently requested part back
		$filescontent = $this->css_split($filescontent);
		
		// Return the currently requested part of the stylesheets
		return $filescontent;
	}
		
    /**
     * Mhtmltime returns the last-modified-timestamp of the MHTML-cache-file
     * 
     * @return integer timestamp of the requested MHTML-cache-file
     * @access public  
     */
	public function mhtmltime()
	{
		$mhtmlfile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',$this->css_source).'_datauri_mhtml_cache.txt';
		if(file_exists($mhtmlfile)) return filemtime($mhtmlfile);
		else return 0;
	}
	
    /**
     * Mhtml reads and returns the contents of the requested MHTML-cache-file
     * 
     * @return string contents of the MHTML-cache-file
     * @access public 
     */
	public function mhtml()
	{
		$mhtmlfile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',$this->css_source).'_datauri_mhtml_cache.txt';
		if(!file_exists($mhtmlfile)) $this->css();
		if(file_exists($mhtmlfile)) return file_get_contents($mhtmlfile);
		else return '';
	}
	
    /**
     * Css_markup creates HTML-<link>-tags for all CSS
     * 
     * @return string the markup
     * @access public 
     */
	public function css_markup()
	{
		// Empty storage for markup to come
		$markup = '';
		
		// Calculate relative path from calling script to booster-folder
		$booster_path = $this->getpath(str_replace('\\','/',dirname(__FILE__)),dirname($_SERVER['SCRIPT_FILENAME']));
		// Calculate relative path from booster-folder to calling script
		$css_path = $this->getpath(dirname($_SERVER['SCRIPT_FILENAME']),str_replace('\\','/',dirname(__FILE__)));
		
		// If sources were defined as array
		if(is_array($this->css_source)) $sources = $this->css_source;
		// If sources were defined as string, convert them into an array
		else $sources = explode(',',$this->css_source);

		// Empty folder/file-storage for full pathed source-files
		$timestamp_dirs = array();
		
		// Fill folder/file-storage-array with prefixed folders/files
		reset($sources);
		for($i=0;$i<sizeof($sources);$i++) 
		{
			$sources[key($sources)] = $css_path.'/'.current($sources);
			array_push($timestamp_dirs,$booster_path.'/'.current($sources));
			next($sources);
		}
		
		// Make sure $source now ends up as string fed from $sources to use as URL-parameter
		$source = implode(',',$sources);
		// Make sure $timestamp_dir now ends up as string fed from $timestamp_dirs to use as URL-parameter
		$timestamp_dir = implode(',',$timestamp_dirs);

		// Insert IE6 fix image flicker
		if($this->browser->family == 'MSIE' && floatval($this->browser->familyversion) < 7 && $this->browser->platform == 'Windows') $markup .= '<script type="text/javascript">try {document.execCommand("BackgroundImageCache", false, true);} catch(err) {}</script>'."\r\n";
	
		// Put together the markup linking to our booster-css-files
		// Append timestamps of the $timestamp_dir to make sure browser reloads once the CSS was updated
		for($j=0;$j<intval($this->css_totalparts);$j++)
		{
			$markup .= '<link rel="'.$this->css_rel.'" media="'.$this->css_media.'" title="'.htmlentities($this->css_title,ENT_QUOTES).'" type="text/css" href="'.$booster_path.'/booster_css.php?dir='.htmlentities($source,ENT_QUOTES).'&amp;totalparts='.intval($this->css_totalparts).'&amp;part='.($j+1).'&amp;nocache='.$this->getfilestime($timestamp_dir,'css').'" '.(($this->css_markuptype == 'XHTML') ? '/' : '').'>'."\r\n";
		}
	
		return $markup;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	

    /**
     * Js_split takes a multiline JS-string and splits it according to @var $css_totalparts and @var $css_part
     * 
     * @param  string    $filescontent contents to split
     * @return string    requested part-number of splitted content
     * @access protected 
     */
	protected function js_split($filescontent = '')
	{
		// If sum of parts is 1 or requested part-number is 0 return full string
		if($this->js_totalparts == 1 || $this->js_part == 0) return $filescontent;
		// Else process string
		else
		{
			// Split at every new line preceeded by a semicolon
			$filescontentlines = explode(";\n",$filescontent);
			// Prepare storage for parts
			$filescontentparts = array();
			$i = 0;
			// Create all parts
			// @todo could maybe be cached, too?
			for($j=0;$j<intval($this->js_totalparts);$j++)
			{
				$filescontentparts[$j] = '';
				while(strlen($filescontentparts[$j]) < ceil(strlen($filescontent) / $this->js_totalparts) && isset($filescontentlines[$i]))
				{
					$filescontentparts[$j] .= $filescontentlines[$i]."\n";
					$i++;
				}
			}
			// Return only the requested part
			return $filescontentparts[$this->js_part - 1];
		}
	}
	
    /**
     * Js fetches and optimizes all stylesheet-files
     * 
     * @return string optimized javascript-code
     * @access public 
     */
	public function js()
	{
		// Call Setcachedir to make sure, cache-path has been calculated
		$this->setcachedir();

		// Empty storage for stylesheet-contents to come
		$filescontent = '';
		// Specify file extension "js" for security reasons
		$type = 'js';
		
		// Prepare @var $sources as an array
		// if @var $js_source is an array
		if(is_array($this->js_source)) $sources = $this->js_source;
		// if @var $js_source is not an array and @var $js_stringmode is not set
		elseif(!$this->js_stringmode) $sources = explode(',',$this->js_source);
		// if @var $js_stringmode is set
		else $sources = array($this->js_source);

		reset($sources);
		for($i=0;$i<sizeof($sources);$i++)
		{
			$source = current($sources);
			// Remove any trailing slash
			$source = rtrim($source,'/');
			
			if($source != '')
			{
				// If current source is a folder or file, get its most recent filetime
				if(is_dir($source) || is_file($source)) $filestime = $this->getfilestime($source,$type,$this->js_recursive);
				// If current source is a string read the filetime from @var $js_stringtime
				else $filestime = $this->js_stringtime;

				// If we are in normal mode use filename of the sourcefile as cache filename
				if(!$this->js_stringmode) $cachefile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',$source).'_'.$type.'_cache.txt';
				// If we are in string mode (which means no available filenames) do an md5 of the contents as cache filename
				else $cachefile = $this->booster_cachedir.'/'.preg_replace('/[^a-z0-9,\-_]/i','',md5($source)).'_datauri_cache.txt';
				
				// If cache-file exists and cache-file date is newer than code-date, read from there
				if(file_exists($cachefile) && filemtime($cachefile) >= $filestime) $filescontent .= file_get_contents($cachefile);
				// There is no cache-file or it is outdated, create it
				else 
				{
					// If current source is a folder or file, get its contents
					if(is_dir($source) || is_file($source)) $currentfilescontent = $this->getfilescontents($source,$type,$this->js_recursive);
					// If current source is already a string
					else $currentfilescontent = $source;

					@file_put_contents($cachefile,$currentfilescontent);
					$filescontent .= $currentfilescontent;
				}
				$filescontent .= "\n";
			}
			next($sources);
		}
		// Split results up
		$filescontent = $this->js_split($filescontent);
		
		// Return the currently requested part of the javascript
		return $filescontent;
	}

    /**
     * Js_markup creates HTML-<script>-tags for all JS
     * 
     * @return string the markup
     * @access public 
     */
	public function js_markup()
	{
		// Empty storage for markup to come
		$markup = '';

		// Calculate relative path from calling script to booster-folder
		$booster_path = $this->getpath(str_replace('\\','/',dirname(__FILE__)),dirname($_SERVER['SCRIPT_FILENAME']));
		// Calculate relative path from booster-folder to calling script
		$js_path = $this->getpath(dirname($_SERVER['SCRIPT_FILENAME']),str_replace('\\','/',dirname(__FILE__)));
		
		// If sources were defined as array
		if(is_array($this->js_source)) $sources = $this->js_source;
		// If sources were defined as string, convert them into an array
		else $sources = explode(',',$this->js_source);

		// Empty folder/file-storage for full pathed source-files
		$timestamp_dirs = array();

		// Fill folder/file-storage-array with prefixed folders/files
		reset($sources);
		for($i=0;$i<sizeof($sources);$i++) 
		{
			$sources[key($sources)] = $js_path.'/'.current($sources);
			array_push($timestamp_dirs,$booster_path.'/'.current($sources));
			next($sources);
		}

		// Make sure $source now ends up as string fed from $sources to use as URL-parameter
		$source = implode(',',$sources);
		// Make sure $timestamp_dir now ends up as string fed from $timestamp_dirs to use as URL-parameter
		$timestamp_dir = implode(',',$timestamp_dirs);

		// Put together the markup linking to our booster-js-files
		// Append timestamps of the $timestamp_dir to make sure browser reloads once the JS was updated
		for($j=0;$j<intval($this->js_totalparts);$j++)
		{
			$markup .= '<script type="text/javascript" src="'.$booster_path.'/booster_js.php?dir='.htmlentities($source,ENT_QUOTES).'&amp;totalparts='.intval($this->js_totalparts).'&amp;part='.($j+1).'&amp;nocache='.$this->getfilestime($timestamp_dir,'js').'"></script>'."\r\n";
		}

		return $markup;
	}
}
?>