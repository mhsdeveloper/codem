<?php



	namespace MHS;

	class Env extends \Publications\Environment {


		const DEBUGGING_MODE_ON = false;

		//this is the installation location of the backend prep and management
		const APP_INSTALL_DIR = SERVER_WWW_ROOT . "html/publications/melon/manage/";

		//URL above domain to backend prep and management
		const APP_INSTALL_URL = "/publications/melon/manage/";

		//this is the installation location of the public delivery of the diary
		const LIVE_INSTALL_DIR = SERVER_WWW_ROOT . "html/publications/melon/";

		//URL above domain for the main public app
		const LIVE_INSTALL_URL = "/publications/melon/";

		//URL prefix (followed by doc ID) for static URL to document view
		const DOC_VIEW_URL_PREFIX = "/publications/melon/index.php/view/";

		//URL prefix (followed by name id) for static URL to view name page
		const NAME_VIEW_URL_PREFIX = "/publications/melon/index.php/";


		//relative URL for uploading files for converting from Word to TEI
		const CONVERT_UPLOAD_URL = "index.php/convert/upload";

		//relative URL to process docx
		const CONVERT_PROCESS_URL = "index.php/convert/process";

		// full path to upload dir
		const CONVERT_UPLOAD_DIR = SERVER_WWW_ROOT . "html/publications/melon/manage/uploads/";

		//relative URL for the ajax call to post a file to upload
		const SOURCE_UPLOAD_URL = "index.php/upload-source";

		//relative folder to the metadata configuration "sets" XML
		const SETS_DEFINITION_SUBFOLDER ="sets/";

		//relative folder to where templates are kept
		const TEMPLATE_SUBFOLDER = "templates/";

		//relative folder to logs
		const LOGFILE_SUBFOLDER = "logs/";

		//full path to XSLT file for processing source on upload
		const XSLT_FILE = SERVER_WWW_ROOT . "html/publications/lib/xsl/prep/mhs-tagging-comp.xsl";

		//full path to the XML source
		const SOURCE_FOLDER = SERVER_WWW_ROOT . "html/publications/melon/xml/";

		//This is the storage path, full path, to where metadata is kept.
		const STORAGE_PATH = SERVER_WWW_ROOT . "html/publications/melon/metadata/";


		//PATH to the metadata folder
		const META_PATH = SERVER_WWW_ROOT . "html/publications/melon/xml/meta/";



		//Views
		const ERROR_VIEW = "error.php";


		//the prefix that precedes the ID in link hrefs
		const DOC_LINK_PREFIX = "/publications/melon/index.php/view/";

		//this tells which segment in the URL holds the doc Link id. the first segment after the router
		// is 1, so for example in this URL: project/index.php/view/VOL01d234 the ID is segment 2
		const DOC_LINK_URL_SEGMENT = 2;

		//prefix for URL's to display of short title definitions
		const SHORT_TITLE_LINK_PREFIX = "/publications/melon/index.php/lists/";

		const SHORT_TITLE_LISTS_PATH = "/publications/melon/xml/lists/";




		const JAVA_BIN = "java";

		//full path to the SAXON jar that supports xslt2.0
		const SAXON_JAR = SERVER_WWW_ROOT . "scripts-offline/saxon8.jar";








		protected function __construct(){

			/* remap the groupID for names to new sortKey and displayString
			 * the key should match and existing key, who's data get's replaced.
			 * In the data, the sortKey should use "-" in place of spaces, and "[+]" in place of dashes
			 * The sortKey will become the output array's key, but the key will become groupID to still
			 * match the TEI source
			*/
			$this->remapNames = array(
			);

			/* names to add to create "See [name]", for maiden names etc. This will automatically be
			 * added, and will not create live links
			 * this array simple gets merged with existing names/groups array before processing/sorting.
			 * foreach array member, the key is the sortKey (the groupID, such as Smith,-Abigail) and
			 * the value is the display such as "Smith, Abigail: see Adams, Abigail"

				e.g.: "Smith,-Abigail" => "Smith, Abigail: see Adams, Abigail"

			 */
			$this->seeNames = array(
			);



			$this->constructContinued();
		}


	}
