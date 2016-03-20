<?php
class msdoc{
	public static function msExcel($path, $colname=array()){
		if(file_exists($path)){
			$fext = explode(".",$path);
			$ext = array_pop($fext);
			if($ext == 'xls'){
				return msdoc::msXls($path, $colname);
			}
			else if($ext == 'xlsx'){
				return msdoc::msXlsx($path,$colname);
			}
		}
	}
	public static function msExcelHeader($path){
		if(file_exists($path)){
			$fext = explode(".",$path);
			$ext = array_pop($fext);
			if($ext == 'xls'){
				return msdoc::msXlsHeader($path);
			}
			else if($ext == 'xlsx'){
				return msdoc::msXlsxHeader($path,$colname);
			}
		}
	}
	public static function msWord(){
		return new clsMsDocGenerator;
	}
	public static function msXls($excel_file_name_with_path, $colname=array()){
		$data = new Spreadsheet_Excel_Reader();
		$document = array();
		// Set output Encoding.
		$data->setOutputEncoding('CP1251');
		$data->read($excel_file_name_with_path);
		$num = '';
		for($i=0; $i<20; $i++){
			if(utility::cleanData($data->sheets[0]['cells'][$i][1]) != ''){
				$num .= $i;
				break;
			}
		}
		$start = !empty($colname)?1:$num+1;
		$altarr = array_values(array_filter($data->sheets[0]['cells'][$num]));
		$altarr = array_map('strtolower',$altarr);
		$colname=!empty($colname)?$colname:$altarr;
		$n=0;
		for ($i = $start; $i <= $data->sheets[0]['numRows']; $i++) {
			++$n;
			for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
			
				//$product[$i-1][$j-1]=$data->sheets[0]['cells'][$i][$j];
				$document[$n-1][$colname[$j-1]]=$data->sheets[0]['cells'][$i][$j];
			}
		}
		return $document;
	}
	public static function msXlsHeader($excel_file_name_with_path, $colname=array()){
		$data = new Spreadsheet_Excel_Reader();
		$document = array();
		// Set output Encoding.
		$data->setOutputEncoding('CP1251');
		$data->read($excel_file_name_with_path);
		$num = '';
		for($i=0; $i<20; $i++){
			if(utility::cleanData($data->sheets[0]['cells'][$i][1]) != ''){
				$num .= $i;
				break;
			}
		}
		$start = !empty($colname)?1:$num+1;
		$altarr = array_values(array_filter($data->sheets[0]['cells'][$num]));
		$altarr = array_map('strtolower',$altarr);
		$colname=!empty($colname)?$colname:$altarr;
		return $colname;
	}
	public static function msXlsx($path, $colname=array()){
		if(file_exists($path)){
			$xlsx = new SimpleXLSX($path);
			$rows = $xlsx->rowsEx();
			$data =  $xlsx->rows();
			//return $data[0];
			$num = '';
			for($i=0; $i<20; $i++){
				if(utility::cleanData($data[$i][0]) != ''){
					$num .= $i;
					break;
				}
			}
			$start = !empty($colname)?1:$num+1;
			$altarr = array_values(array_filter($data[$num]));
			$altarr = array_map('strtolower',$altarr);
			$colname=!empty($colname)?$colname:$altarr;
			$n=0;
			//$colname=!empty($colname)?$colname:array('id','stage','plot','name','phone','email','address','commitment','paid');
			
			for ($i = $start; $i <= count($rows); $i++) {
				$n++;
				for ($j = 0; $j <= count($rows[$i]); $j++) {
					$document[$n][$colname[$j]]=$rows[$i][$j]['value'];
				}
			}
			return $document;
		}
		echo $path;
	}
	public static function msXlsxHeader($path, $colname=array()){
		if(file_exists($path)){
			$xlsx = new SimpleXLSX($path);
			$rows = $xlsx->rowsEx();
			$data =  $xlsx->rows();
			//return $data[0];
			$num = '';
			for($i=0; $i<20; $i++){
				if(utility::cleanData($data[$i][0]) != ''){
					$num .= $i;
					break;
				}
			}
			$start = !empty($colname)?1:$num+1;
			$altarr = array_values(array_filter($data[$num]));
			$altarr = array_map('strtolower',$altarr);
			$colname=!empty($colname)?$colname:$altarr;
			return $colname;
		}
		echo $path;
	}
}

class clsMsDocGenerator{
	var $appName = 'MsDocGenerator';
	var $appVersion = '0.4';
	var $isDebugging = false;
	
	var $leftMargin;
	var $rightMargin;
	var $topMargin;
	var $bottomMargin;
	var $pageOrientation;
	var $pageType;
	
	var $documentLang;
	var $documentCharset;
	var $fontFamily;
	var $fontSize;
	
	var $documentBuffer;
	var $formatBuffer;
	var $cssFile;
	var $lastSessionNumber;
	var $lastPageNumber;
	var $atualPageWidth;
	var $atualPageHeight;
	
	var $tableIsOpen;
	var $tableLastRow;
	var $tableBorderAlt;
	var $tablePaddingAltRight;
	var $tablePaddingAltLeft;
	var $tableBorderInsideH;
	var $tableBorderInsideV;
	
	var $numImages;

	
	/**
	 * constructor clsMsDocGenerator(const $pageOrientation = 'PORTRAIT', const $pageType = 'A4',  string $cssFile = '', int $topMargin = 3.0, int $rightMargin = 2.5, int $bottomMargin = 3.0, int $leftMargin = 2.5)
	 * @param $pageOrientation: The orientation of the pages of the initial session, 'PORTRAIT' or 'LANDSCAPE'
	 * @param $pageType: The initial type of the paper of the pages of the session
	 * @param $cssFile: extra file with formating configurations, in css file format
	 * @param $topMargin: top margin of the document
	 * @param $rightMargin: right margin of the document
	 * @param $bottomMargin: bottom margin of the document
	 * @param $leftMargin: left margin of the document 
	 */
	function clsMsDocGenerator($pageOrientation = 'PORTRAIT', $pageType = 'A4', $cssFile = '', $topMargin = 3.0, $rightMargin = 2.5, $bottomMargin = 3.0, $leftMargin = 2.5){
		$this->documentBuffer = '';
		$this->formatBuffer = '';
		$this->cssFile = $cssFile;
		$this->lastSessionNumber = 0;
		$this->lastPageNumber = 0;
		$this->atualPageWidth = 0;
		$this->atualPageHeight = 0;
		
		$this->tableIsOpen = false;
		$this->tableLastRow = 0;
		$this->tableBorderAlt = 0.5;
		$this->tablePaddingAltRight = 5.4;
		$this->tablePaddingAltLeft = 5.4;
		$this->tableBorderInsideH = 0.5;
		$this->tableBorderInsideV = 0.5;

		$this->documentLang = 'EN';
		$this->documentCharset = 'windows-1252';
		$this->fontFamily = '"Calibri","sans-serif"';
		$this->fontSize = '11.0pt';
		
		$this->pageOrientation = $pageOrientation;
		$this->pageType = $pageType;
		
		$this->topMargin = $topMargin;
		$this->rightMargin = $rightMargin;
		$this->bottomMargin = $bottomMargin;
		$this->leftMargin = $leftMargin;
		
		$this->numImages =0;
		
		$this->newSession($this->pageOrientation, $this->pageType, $this->topMargin, $this->rightMargin, $this->bottomMargin, $this->leftMargin);
		$this->newPage();
	}//end clsMsDocGenerator()
	
	/**
	 * public int newSession(const $pageOrientation = NULL, const $pageType = NULL, int $topMargin = NULL, int $rightMargin = NULL, int $bottomMargin = NULL, int $leftMargin = NULL)
	 * @param $pageOrientation: The orientation of the pages of the this session, 'PORTRAIT' or 'LANDSCAPE'
	 * @param $pageType: The type of the paper of the pages of the this session
	 * @param $topMargin: top margin of the this session
	 * @param $rightMargin: right margin of the this session
	 * @param $bottomMargin: bottom margin of the this session
	 * @param $leftMargin: left margin of the this session
	 * @return int: the number of the new session
	 */
	function newSession($pageOrientation = NULL, $pageType = NULL, $topMargin = NULL, $rightMargin = NULL, $bottomMargin = NULL, $leftMargin = NULL){
		//don't setted now? then use document start values
		$pageOrientation = $pageOrientation === NULL ? $this->pageOrientation : $pageOrientation;
		$pageType = $pageType === NULL ? $this->pageType : $pageType;
		$topMargin = $topMargin === NULL ? $this->topMargin : $topMargin;
		$rightMargin = $rightMargin === NULL ? $this->rightMargin : $rightMargin;
		$bottomMargin = $bottomMargin === NULL ? $this->bottomMargin : $bottomMargin;
		$leftMargin = $leftMargin === NULL ? $this->leftMargin : $leftMargin;

		$this->lastSessionNumber++;
		
		if($this->lastSessionNumber != 1){
			$this->endSession();
			$this->documentBuffer .= "<br clear=\"all\" style=\"page-break-before: always; mso-break-type: section-break\">\n";
		}

		switch($pageOrientation){
			case 'PORTRAIT' :
				switch($pageType){
					case 'A4' :
						$this->atualPageWidth = A4_WIDTH * One_Cent;
						$this->atualPageHeight = A4_HEIGHT * One_Cent;
						break;
					case 'A5' :
						$this->atualPageWidth = A5_WIDTH * One_Cent;
						$this->atualPageHeight = A5_HEIGHT * One_Cent;
						break;
					case 'LETTER' :
						$this->atualPageWidth = LETTER_WIDTH * One_Cent;
						$this->atualPageHeight = LETTER_HEIGHT * One_Cent;
						break;
					case 'OFFICE' :
						$this->atualPageWidth = OFFICE_WIDTH * One_Cent;
						$this->atualPageHeight = OFFICE_HEIGHT * One_Cent;
						break;
					default:
						die("ERROR: PAGE TYPE ($pageType) IS NOT DEFINED");
				}
				$msoPageOrientation = 'portrait';
				break;
			case 'LANDSCAPE' :
				switch($pageType){
					case 'A4' :
						$this->atualPageWidth = A4_HEIGHT * One_Cent;
						$this->atualPageHeight = A4_WIDTH * One_Cent;
						break;
					case 'A5' :
						$this->atualPageWidth = A5_HEIGHT * One_Cent;
						$this->atualPageHeight = A5_WIDTH * One_Cent;
						break;
					case 'LETTER' :
						$this->atualPageWidth = LETTER_HEIGHT * One_Cent;
						$this->atualPageHeight = LETTER_WIDTH * One_Cent;
						break;
					case 'OFFICE' :
						$this->atualPageWidth = OFFICE_HEIGHT * One_Cent;
						$this->atualPageHeight = OFFICE_WIDTH * One_Cent;
						break;
					default:
						die("ERROR: PAGE TYPE ($pageType) IS NOT DEFINED");
				}
				$msoPageOrientation = 'landscape';
				break;
			default :
				die("ERROR: INVALID PAGE ORIENTATION ($pageOrientation)");
		}
		$pageSize = "{$this->atualPageWidth}pt {$this->atualPageHeight}pt";
		$pageMargins = "{$topMargin}cm {$rightMargin}cm {$bottomMargin}cm {$leftMargin}cm";
		
		$sessionName = "Section" . $this->lastSessionNumber;
		
		$this->formatBuffer .= "@page $sessionName\n";
		$this->formatBuffer .= "   {size: $pageSize;\n";
		$this->formatBuffer .= "   mso-page-orientation: $msoPageOrientation;\n";
		$this->formatBuffer .= "   margin: $pageMargins;\n";
		$this->formatBuffer .= "   mso-header-margin: 36pt;\n";
		$this->formatBuffer .= "   mso-footer-margin: 36pt;\n";
		$this->formatBuffer .= "   mso-paper-source: 0;}\n";
		$this->formatBuffer .= "div.$sessionName\n";
		$this->formatBuffer .= "  {page: $sessionName;}\n\n";
		
		$this->documentBuffer .= "<div class=\"$sessionName\">\n";
		
		return $this->lastSessionNumber;
	}//end newSession()
	
	/**
	 * public int newPage(void)
	 * @return int: the number of the new page
	 */	
	function newPage(){
		$this->lastPageNumber++;
		if($this->lastPageNumber != 1)
			$this->documentBuffer .= "<br clear=\"all\" style=\"page-break-before: always;\">";
		return $this->lastPageNumber;
	}//end newPage()
	
	/**
	 * public void output(string $fileName = '', string $saveInPath = '')
	 * @param $fileName: the file name of document
	 * @param $saveInPath: if not empty will be the path to save document otherwise show
	 */
	function output($fileName = '', $saveInPath = ''){
		$this->endSession();
		
		$outputCode = '';
		$outputCode .= "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n";
		$outputCode .= "   xmlns:w=\"urn:schemas-microsoft-com:office:word\"\n";
		$outputCode .= "   xmlns=\"http://www.w3.org/TR/REC-html40\">\n";
		
		$outputCode .= $this->getHeader();
		
		$outputCode .= $this->getBody();
		
		$outputCode .= "</html>\n";
		
		$fileName = $fileName != '' ? $fileName : basename($_SERVER['PHP_SELF'], '.php') . '.doc';

		if($saveInPath == ''){
			if($this->isDebugging){
				echo nl2br(htmlentities($outputCode));
			}else{
				header("Content-Type: application/msword; charset=\$this->documentCharset\"");
				
				header("Content-Disposition: attachment; filename=\"$fileName\"");
				
				echo $outputCode;	
			}
		}else{
			if(substr($saveInPath,-1) <> "/")
				$saveInPath = $saveInPath."/";
			file_put_contents($saveInPath . $fileName, $outputCode);
		}
	}//end output()
	
	/**
	 * public void setDocumentLang(string $lang)
	 * @param $lang: document lang
	 */
	function setDocumentLang($lang){
		$this->documentLang = $lang;
	}//end setDocumentLang()
	
	/**
	 * public void setDocumentCharset(string $charset)
	 * @param $charset: document charset
	 */
	function setDocumentCharset($charset){
		$this->documentCharset = $charset;
	}//end setDocumentCharset()
	
	/**
	 * public void setFontFamily(string $fontFamily)
	 * @param $fontFamily: default document font family
	 */
	function setFontFamily($fontFamily){
		$this->fontFamily = $fontFamily;
	}//end setFontFamily()
	
	/**
	 * public void setFontSize(string $fontSize)
	 * @param $fontSize: default document font Size
	 */
	function setFontSize($fontSize){
		$this->fontSize = $fontSize;
	}//end setFontSize()
	
	/**
	 * public void addParagraph(string $content, array $inlineStyle = NULL, string $className = 'normalText')
	 * @param $content: content of the paragraph
	 * @param $inlineStyle: array of css block properties
	 * #param $className: class name of any class defined in extra format file
	 */
	function addParagraph2($content, $inlineStyle = NULL, $className = 'normalText'){
		$style = '';
		if(is_array($inlineStyle)){
			foreach($inlineStyle as $key => $value)
				$style .= "$key: $value;";
		}
		$this->documentBuffer .= "<p class=\"$className\"" . ($style != '' ? " style=\"$style\"" : '') . ">".($content == '' ? '<o:p></o:p>' : $content)."</p>\n";
	}//end addParagraph()
	function addParagraph($content, $inlineStyle = NULL, $className = 'normalText'){
		$style = '';
		if(is_array($inlineStyle)){
			foreach($inlineStyle as $key => $value)
				$style .= "$key: $value;";
		}
		$this->documentBuffer .= "<span class=\"$className\"" . ($style != '' ? " style=\"$style\"" : '') . ">".($content == '' ? '<o:p></o:p>' : $content)."</span><br />\n";
	}//end addParagraph()
	
	function addSpan($content, $inlineStyle = NULL, $className = 'normalText'){
		$style = '';
		if(is_array($inlineStyle)){
			foreach($inlineStyle as $key => $value)
				$style .= "$key: $value;";
		}
		$this->documentBuffer .= "<span class=\"$className\"" . ($style != '' ? " style=\"$style\"" : '') . ">".($content == '' ? '' : $content)."</span>\n";
	}//end addParagraph()
	
	/**
	 * void bufferImage(string $imagePath, int $width, int $height, string $title = ''){
	 * @param $imagePath: url of the image
	 * @param $width: width to show image in pixels
	 * @param $height: height to show image in pixels
	 */
	function bufferImage($imagePath, $width, $height, $title = ''){
		$this->numImages++;
		$buffer = "<!--[if gte vml 1]>";
		if($this->numImages == 1){
			$buffer .= "<v:shapetype id=\"_x0000_t75\" coordsize=\"21600,21600\"
		   o:spt=\"75\" o:preferrelative=\"t\" path=\"m@4@5l@4@11@9@11@9@5xe\" filled=\"f\"
		   stroked=\"f\">
		   <v:stroke joinstyle=\"miter\"/>
		   <v:formulas>
			<v:f eqn=\"if lineDrawn pixelLineWidth 0\"/>
			<v:f eqn=\"sum @0 1 0\"/>
			<v:f eqn=\"sum 0 0 @1\"/>
			<v:f eqn=\"prod @2 1 2\"/>
			<v:f eqn=\"prod @3 21600 pixelWidth\"/>
			<v:f eqn=\"prod @3 21600 pixelHeight\"/>
			<v:f eqn=\"sum @0 0 1\"/>
			<v:f eqn=\"prod @6 1 2\"/>
			<v:f eqn=\"prod @7 21600 pixelWidth\"/>
			<v:f eqn=\"sum @8 21600 0\"/>
			<v:f eqn=\"prod @7 21600 pixelHeight\"/>
			<v:f eqn=\"sum @10 21600 0\"/>
		   </v:formulas>
		   <v:path o:extrusionok=\"f\" gradientshapeok=\"t\" o:connecttype=\"rect\"/>
		   <o:lock v:ext=\"edit\" aspectratio=\"t\"/>
		  </v:shapetype>";
		}
		$buffer .= "<v:shape id=\"_x0000_i102{$this->numImages}\" type=\"#_x0000_t75\" style='width:".$this->pixelsToPoints($width)."pt;
		   height:".$this->pixelsToPoints($height)."pt'>
		   <v:imagedata src=\"$imagePath\" o:title=\"accessibilityIssues\"/>
		  </v:shape><![endif]--><![if !vml]><img width=\"$width\" height=\"$height\" src=\"$imagePath\" v:shapes=\"_x0000_i102{$this->numImages}\"><![endif]>";
		return $buffer;
	}//end bufferImage()
	
	/**
	 * void addImage(string $imagePath, int $width, int $height, string $title = ''){
	 * @param $imagePath: url of the image
	 * @param $width: width to show image in pixels
	 * @param $height: height to show image in pixels
	 */
	function addImage($imagePath, $width, $height, $title = ''){
		$this->documentBuffer.= $this->bufferImage($imagePath, $width, $height, $title);
	}//end addImage()
	
	/**
	 * public void startTable(array $inlineStyle = NULL, string $className = 'normalTable')
	 * @param $inlineStyle: array of css table properties, property => value
	 * @param $className: class name of any class defined, may be in extra format file
	 */
	function startTable($inlineStyle = NULL, $className = 'normalTable'){
		$style = '';
		if(is_array($inlineStyle)){
			foreach($inlineStyle as $key => $value)
				$style .= "$key: $value;";
		}
		$this->documentBuffer .= "<table class=\"$className\" style=\"$style\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n";
		
		$this->tableIsOpen = true;
	}//end startTable()
	
	/**
	 * public int addTableRow(array $cells, array $aligns = NULL, array $vAligns = NULL, array $inlineStyle = NULL, array $classesName = NULL)
	 * @param $cells: array with content of cells of the row
	 * @param $aligns: array with align cell constants in html style, a item for each cell item
	 * @param $vAligns: array with vertical align cell constants in html style, a item for each cell item
	 * @param $inlineStyle: array of css block properties, property => value
	 * @param $classesName: array with class name of any class defined in extra format file, a item for each cell item
	 */
	function addTableRow($cells, $aligns = NULL, $vAligns = NULL, $inlineStyle = NULL, $classesName = NULL){
		if(! $this->tableIsOpen)
			die('ERROR: TABLE IS NOT STARTED');
			
		if(is_array($classesName) && count($classesName) != count($cells))
			die('ERROR: COUNT OF CLASSES IS DIFERENT OF COUNT OF CELLS');
		if(is_array($aligns) && count($aligns) != count($cells))
			die('ERROR: COUNT OF ALIGNS IS DIFERENT OF COUNT OF CELLS');
		if(is_array($vAligns) && count($vAligns) != count($cells))
			die('ERROR: COUNT OF VALIGNS IS DIFERENT OF COUNT OF CELLS');
		
		$style = '';
		if(is_array($inlineStyle)){
			foreach($inlineStyle as $key => $value)
				$style .= "$key: $value;";
		}
		
		$tableWidth = $this->atualPageWidth;// - ($this->leftMargin * One_Cent + $this->rightMargin * One_Cent);
		//$tableWidth -= (BORDER_ALT*2 + PADDING_ALT_RIGHT + PADDING_ALT_LEFT + BORDER_INSIDEH*2 + BORDER_INSIDEV*2);
		$cellWidth = floor($tableWidth / count($cells));
		
		
		$this->documentBuffer .= "<tr style=\"mso-yfti-irow: $this->tableLastRow\">\n";
		for($i = 0; $i < count($cells); $i++){
			$align = is_array($aligns) ? $aligns[$i] : 'left';
			$vAlign = is_array($vAligns) ? $vAligns[$i] : 'top';
			$classAttr = is_array($classesName) ? " class=\"$classesName[$i]\"" : '';
			
			$this->documentBuffer .= "<td width=\"$cellWidth\" align=\"$align\" valign=\"$vAlign\" style=\"$style\"{$classAttr}>$cells[$i]</td>\n";
		}
		$this->documentBuffer .= "</tr>\n";
		
		$this->tableLastRow++;
		return $this->tableLastRow;
	}//end addTableRow()
	
	/**
	 * public void endTable(void)
	 */
	function endTable(){
		if(! $this->tableIsOpen)
			die('ERROR: TABLE IS NOT STARTED');
			
		$this->documentBuffer .= "</table>\n";
		
		$this->tableIsOpen = false;
		$this->tableLastRow = 0;
	}//end endTable()


	/****************************************************
	 * begin private functions
	 ***************************************************/
	
	/**
	 * private void endSession(void)
	 */
	function endSession(){
		$this->documentBuffer .= "</div>\n";
	}//end newSession()	
	
	/**
	 * private float endSession(int $pixels)
	 * @param $pixels: number of pixels to convert
	 */
	function pixelsToPoints($pixels){
		$points = 0.75 * floatval($pixels);
		return number_format($points,2);
	}//end pixelsToPoints()
	
	/**
	 * private void prepareDefaultHeader(void)
	 */
	function prepareDefaultHeader(){	
		$this->formatBuffer .= "p.normalText, li.normalText, div.normalText{\n";
		$this->formatBuffer .= "   mso-style-parent: \"\";\n";
		$this->formatBuffer .= "   margin: 0cm;\n";
		$this->formatBuffer .= "   margin-bottom: 6pt;\n";
		$this->formatBuffer .= "   mso-pagination: widow-orphan;\n";
		$this->formatBuffer .= "   font-size: {$this->fontSize}pt;\n";
		$this->formatBuffer .= "   font-family: \"{$this->fontFamily}\";\n";
		$this->formatBuffer .= "   mso-fareast-font-family: \"{$this->fontFamily}\";\n";
		$this->formatBuffer .= "   line-height:115%;\n";
		$this->formatBuffer .= "}\n\n";
		
		$this->formatBuffer .= "table.normalTable{\n";
		$this->formatBuffer .= "   mso-style-name: \"Tabela com grade\";\n";
		$this->formatBuffer .= "   mso-tstyle-rowband-size: 0;\n";
		$this->formatBuffer .= "   mso-tstyle-colband-size: 0;\n";
		$this->formatBuffer .= "   border-collapse: collapse;\n";
		$this->formatBuffer .= "   mso-border-alt: solid windowtext {$this->tableBorderAlt}pt;\n";
		$this->formatBuffer .= "   mso-yfti-tbllook: 480;\n";
		$this->formatBuffer .= "   mso-padding-alt: 0cm {$this->tablePaddingAltRight}pt 0cm {$this->tablePaddingAltLeft}pt;\n";
		$this->formatBuffer .= "   mso-border-insideh: {$this->tableBorderInsideH}pt solid windowtext;\n";
		$this->formatBuffer .= "   mso-border-insidev: {$this->tableBorderInsideV}pt solid windowtext;\n";
		$this->formatBuffer .= "   mso-para-margin: 0cm;\n";
		$this->formatBuffer .= "   mso-para-margin-bottom: .0001pt;\n";
		$this->formatBuffer .= "   mso-pagination: widow-orphan;\n";
		$this->formatBuffer .= "   font-size: {$this->fontSize}pt;\n";
		$this->formatBuffer .= "   font-family: \"{$this->fontFamily}\";\n";
		$this->formatBuffer .= "}\n";
		$this->formatBuffer .= "table.normalTable td{\n";
		$this->formatBuffer .= "   border: solid windowtext 1.0pt;\n";
		$this->formatBuffer .= "   border-left: none;\n";
		$this->formatBuffer .= "   mso-border-left-alt: solid windowtext .5pt;\n";
		$this->formatBuffer .= "   mso-border-alt: solid windowtext .5pt;\n";
		$this->formatBuffer .= "   padding: 0cm 5.4pt 0cm 5.4pt;\n";
		$this->formatBuffer .= "}\n\n";

		$this->formatBuffer .= "table.tableWithoutGrid{\n";
		$this->formatBuffer .= "   mso-style-name: \"Tabela sem grade\";\n";
		$this->formatBuffer .= "   mso-tstyle-rowband-size: 0;\n";
		$this->formatBuffer .= "   mso-tstyle-colband-size: 0;\n";
		$this->formatBuffer .= "   border-collapse: collapse;\n";
		$this->formatBuffer .= "   border: none;\n";
		$this->formatBuffer .= "   mso-border-alt: none;\n";
		$this->formatBuffer .= "   mso-yfti-tbllook: 480;\n";
		$this->formatBuffer .= "   mso-padding-alt: 0cm {$this->tablePaddingAltRight}pt 0cm {$this->tablePaddingAltLeft}pt;\n";
		$this->formatBuffer .= "   mso-border-insideh: {$this->tableBorderInsideH}pt solid windowtext;\n";
		$this->formatBuffer .= "   mso-border-insidev: {$this->tableBorderInsideV}pt solid windowtext;\n";
		$this->formatBuffer .= "   mso-para-margin: 0cm;\n";
		$this->formatBuffer .= "   mso-para-margin-bottom: .0001pt;\n";
		$this->formatBuffer .= "   mso-pagination: widow-orphan;\n";
		$this->formatBuffer .= "   font-size: {$this->fontSize}pt;\n";
		$this->formatBuffer .= "   font-family: \"{$this->fontFamily}\";\n";
		$this->formatBuffer .= "}\n\n";
		
		if($this->cssFile != ''){
			if(file_exists($this->cssFile))
				$this->formatBuffer .= file_get_contents($this->cssFile);
		}
	}//end prepareDefaultHeader()
	
	/**
	 * private string getHeader(void)
	 */
	function getHeader(){
		$header = '';
		$header .= "<head>\n";
		$header .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$this->documentCharset\">\n";
		$header .= "<meta name=\"ProgId\" content=\"Word.Document\">\n";
		$header .= "<meta name=\"Generator\" content=\"$this->appName $this->appVersion\">\n";
		$header .= "<meta name=\"Originator\" content=\"$this->appName $this->appVersion\">\n";
		$header .= "<!--[if !mso]>\n";
		$header .= "<style>\n";
		$header .= "v\:* {behavior:url(#default#VML);}\n";
		$header .= "o\:* {behavior:url(#default#VML);}\n";
		$header .= "w\:* {behavior:url(#default#VML);}\n";
		$header .= ".shape {behavior:url(#default#VML);}\n";
		$header .= "</style>\n";
		$header .= "<![endif]-->\n";

		$header .= "<style>\n";
		$header .= "<!--\n";
		$header .= "/* Style Definitions */\n";
		
		$this->prepareDefaultHeader();
		
		$header .= $this->formatBuffer ."\n";
		
		$header .= "-->\n";
		$header .= "</style>\n";
		$header .= "</head>\n";
		
		return $header;
	}//end getHeader()
	
	function addExtraSpace(){
		$this->documentBuffer .= "<p class=\"$className\" ></p><br />\n";
	}
	
	/**
	 * private string getBody(void)
	 */
	function getBody(){
		$body = '';
		$body .= "<body lang=\"$this->documentLang\" style=\"tab-interval: 35.4pt\">\n";
		
		$body .= $this->documentBuffer . "\n";
		
		$body .= "</body>\n";
		
		return $body;
	}//end getBody()
}//end class clsMsDocGenerator


/****************************************************
 * constant definition
 ***************************************************/
define('One_Cent', 28.35);//1cm = 28.35pt

//paper sizes in cm
define('A4_WIDTH', 21.0);
define('A4_HEIGHT', 29.7);
define('A5_WIDTH', 14.8);
define('A5_HEIGHT', 21.0);
define('LETTER_WIDTH', 21.59);
define('LETTER_HEIGHT', 27.94);
define('OFFICE_WIDTH', 21.59);
define('OFFICE_HEIGHT', 35.56);


/****************************************************
 * functions definition
 ***************************************************/
 
if(! function_exists('file_get_contents')){
  function file_get_contents($filename, $useIncludePath = '', $context = ''){
    if(empty($useIncludePath)){
      return implode('',file($filename));
    }elseif(empty($content)){
      return implode('',file($filename, $useIncludePath));
    }else{
      return implode('',file($filename, $useIncludePath, $content));
    }
  }//end file_get_contents()
}//end if

if(! function_exists('file_put_contents')){
  function file_put_contents($filename, $data){
    $file = fopen($filename, 'wb');
    $return = fwrite($file, $data);
    fclose($file);
	return $return;
  }//end file_put_contents()
}//end if

/***************************************************
* MS Excel Reader
*/

define('NUM_BIG_BLOCK_DEPOT_BLOCKS_POS', 0x2c);
define('SMALL_BLOCK_DEPOT_BLOCK_POS', 0x3c);
define('ROOT_START_BLOCK_POS', 0x30);
define('BIG_BLOCK_SIZE', 0x200);
define('SMALL_BLOCK_SIZE', 0x40);
define('EXTENSION_BLOCK_POS', 0x44);
define('NUM_EXTENSION_BLOCK_POS', 0x48);
define('PROPERTY_STORAGE_BLOCK_SIZE', 0x80);
define('BIG_BLOCK_DEPOT_BLOCKS_POS', 0x4c);
define('SMALL_BLOCK_THRESHOLD', 0x1000);
// property storage offsets
define('SIZE_OF_NAME_POS', 0x40);
define('TYPE_POS', 0x42);
define('START_BLOCK_POS', 0x74);
define('SIZE_POS', 0x78);
define('IDENTIFIER_OLE', pack("CCCCCCCC",0xd0,0xcf,0x11,0xe0,0xa1,0xb1,0x1a,0xe1));

//echo 'ROOT_START_BLOCK_POS = '.ROOT_START_BLOCK_POS."\n";

//echo bin2hex($data[ROOT_START_BLOCK_POS])."\n";
//echo "a=";
//echo $data[ROOT_START_BLOCK_POS];
//function log

function GetInt4d($data, $pos) {
        return ord($data[$pos]) | (ord($data[$pos+1]) << 8) | (ord($data[$pos+2]) << 16) | (ord($data[$pos+3]) << 24); 
}


class OLERead {
    var $data = '';
    
    
    function OLERead(){
        
        
    }
    
    function read($sFileName){
        
    	// check if file exist and is readable (Darko Miljanovic)
    	if(!is_readable($sFileName)) {
    		$this->error = 1;
    		return false;
    	}
    	
    	$this->data = @file_get_contents($sFileName);
    	if (!$this->data) { 
    		$this->error = 1; 
    		return false; 
   		}
   		//echo IDENTIFIER_OLE;
   		//echo 'start';
   		if (substr($this->data, 0, 8) != IDENTIFIER_OLE) {
    		$this->error = 1; 
    		return false; 
   		}
        $this->numBigBlockDepotBlocks = GetInt4d($this->data, NUM_BIG_BLOCK_DEPOT_BLOCKS_POS);
        $this->sbdStartBlock = GetInt4d($this->data, SMALL_BLOCK_DEPOT_BLOCK_POS);
        $this->rootStartBlock = GetInt4d($this->data, ROOT_START_BLOCK_POS);
        $this->extensionBlock = GetInt4d($this->data, EXTENSION_BLOCK_POS);
        $this->numExtensionBlocks = GetInt4d($this->data, NUM_EXTENSION_BLOCK_POS);
        
	/*
        echo $this->numBigBlockDepotBlocks." ";
        echo $this->sbdStartBlock." ";
        echo $this->rootStartBlock." ";
        echo $this->extensionBlock." ";
        echo $this->numExtensionBlocks." ";
        */
        //echo "sbdStartBlock = $this->sbdStartBlock\n";
        $bigBlockDepotBlocks = array();
        $pos = BIG_BLOCK_DEPOT_BLOCKS_POS;
       // echo "pos = $pos";
	$bbdBlocks = $this->numBigBlockDepotBlocks;
        
            if ($this->numExtensionBlocks != 0) {
                $bbdBlocks = (BIG_BLOCK_SIZE - BIG_BLOCK_DEPOT_BLOCKS_POS)/4; 
            }
        
        for ($i = 0; $i < $bbdBlocks; $i++) {
              $bigBlockDepotBlocks[$i] = GetInt4d($this->data, $pos);
              $pos += 4;
        }
        
        
        for ($j = 0; $j < $this->numExtensionBlocks; $j++) {
            $pos = ($this->extensionBlock + 1) * BIG_BLOCK_SIZE;
            $blocksToRead = min($this->numBigBlockDepotBlocks - $bbdBlocks, BIG_BLOCK_SIZE / 4 - 1);

            for ($i = $bbdBlocks; $i < $bbdBlocks + $blocksToRead; $i++) {
                $bigBlockDepotBlocks[$i] = GetInt4d($this->data, $pos);
                $pos += 4;
            }   

            $bbdBlocks += $blocksToRead;
            if ($bbdBlocks < $this->numBigBlockDepotBlocks) {
                $this->extensionBlock = GetInt4d($this->data, $pos);
            }
        }

       // var_dump($bigBlockDepotBlocks);
        
        // readBigBlockDepot
        $pos = 0;
        $index = 0;
        $this->bigBlockChain = array();
        
        for ($i = 0; $i < $this->numBigBlockDepotBlocks; $i++) {
            $pos = ($bigBlockDepotBlocks[$i] + 1) * BIG_BLOCK_SIZE;
            //echo "pos = $pos";	
            for ($j = 0 ; $j < BIG_BLOCK_SIZE / 4; $j++) {
                $this->bigBlockChain[$index] = GetInt4d($this->data, $pos);
                $pos += 4 ;
                $index++;
            }
        }

	//var_dump($this->bigBlockChain);
        //echo '=====2';
        // readSmallBlockDepot();
        $pos = 0;
	    $index = 0;
	    $sbdBlock = $this->sbdStartBlock;
	    $this->smallBlockChain = array();
	
	    while ($sbdBlock != -2) {
	
	      $pos = ($sbdBlock + 1) * BIG_BLOCK_SIZE;
	
	      for ($j = 0; $j < BIG_BLOCK_SIZE / 4; $j++) {
	        $this->smallBlockChain[$index] = GetInt4d($this->data, $pos);
	        $pos += 4;
	        $index++;
	      }
	
	      $sbdBlock = $this->bigBlockChain[$sbdBlock];
	    }

        
        // readData(rootStartBlock)
        $block = $this->rootStartBlock;
        $pos = 0;
        $this->entry = $this->__readData($block);
        
        /*
        while ($block != -2)  {
            $pos = ($block + 1) * BIG_BLOCK_SIZE;
            $this->entry = $this->entry.substr($this->data, $pos, BIG_BLOCK_SIZE);
            $block = $this->bigBlockChain[$block];
        }
        */
        //echo '==='.$this->entry."===";
        $this->__readPropertySets();

    }
    
     function __readData($bl) {
        $block = $bl;
        $pos = 0;
        $data = '';
        
        while ($block != -2)  {
            $pos = ($block + 1) * BIG_BLOCK_SIZE;
            $data = $data.substr($this->data, $pos, BIG_BLOCK_SIZE);
            //echo "pos = $pos data=$data\n";	
	    $block = $this->bigBlockChain[$block];
        }
		return $data;
     }
        
    function __readPropertySets(){
        $offset = 0;
        //var_dump($this->entry);
        while ($offset < strlen($this->entry)) {
              $d = substr($this->entry, $offset, PROPERTY_STORAGE_BLOCK_SIZE);
            
              $nameSize = ord($d[SIZE_OF_NAME_POS]) | (ord($d[SIZE_OF_NAME_POS+1]) << 8);
              
              $type = ord($d[TYPE_POS]);
              //$maxBlock = strlen($d) / BIG_BLOCK_SIZE - 1;
        
              $startBlock = GetInt4d($d, START_BLOCK_POS);
              $size = GetInt4d($d, SIZE_POS);
        
            $name = '';
            for ($i = 0; $i < $nameSize ; $i++) {
              $name .= $d[$i];
            }
            
            $name = str_replace("\x00", "", $name);
            
            $this->props[] = array (
                'name' => $name, 
                'type' => $type,
                'startBlock' => $startBlock,
                'size' => $size);

            if (($name == "Workbook") || ($name == "Book")) {
                $this->wrkbook = count($this->props) - 1;
            }

            if ($name == "Root Entry") {
                $this->rootentry = count($this->props) - 1;
            }
            
            //echo "name ==$name=\n";

            
            $offset += PROPERTY_STORAGE_BLOCK_SIZE;
        }   
        
    }
    
    
    function getWorkBook(){
    	if ($this->props[$this->wrkbook]['size'] < SMALL_BLOCK_THRESHOLD){
//    	  getSmallBlockStream(PropertyStorage ps)

			$rootdata = $this->__readData($this->props[$this->rootentry]['startBlock']);
	        
			$streamData = '';
	        $block = $this->props[$this->wrkbook]['startBlock'];
	        //$count = 0;
	        $pos = 0;
		    while ($block != -2) {
      	          $pos = $block * SMALL_BLOCK_SIZE;
		          $streamData .= substr($rootdata, $pos, SMALL_BLOCK_SIZE);

			      $block = $this->smallBlockChain[$block];
		    }
			
		    return $streamData;
    		

    	}else{
    	
	        $numBlocks = $this->props[$this->wrkbook]['size'] / BIG_BLOCK_SIZE;
	        if ($this->props[$this->wrkbook]['size'] % BIG_BLOCK_SIZE != 0) {
	            $numBlocks++;
	        }
	        
	        if ($numBlocks == 0) return '';
	        
	        //echo "numBlocks = $numBlocks\n";
	    //byte[] streamData = new byte[numBlocks * BIG_BLOCK_SIZE];
	        //print_r($this->wrkbook);
	        $streamData = '';
	        $block = $this->props[$this->wrkbook]['startBlock'];
	        //$count = 0;
	        $pos = 0;
	        //echo "block = $block";
	        while ($block != -2) {
	          $pos = ($block + 1) * BIG_BLOCK_SIZE;
	          $streamData .= substr($this->data, $pos, BIG_BLOCK_SIZE);
	          $block = $this->bigBlockChain[$block];
	        }   
	        //echo 'stream'.$streamData;
	        return $streamData;
    	}
    }
    
}

//define('Spreadsheet_Excel_Reader_HAVE_ICONV', function_exists('iconv'));
//define('Spreadsheet_Excel_Reader_HAVE_MB', function_exists('mb_convert_encoding'));

define('Spreadsheet_Excel_Reader_BIFF8', 0x600);
define('Spreadsheet_Excel_Reader_BIFF7', 0x500);
define('Spreadsheet_Excel_Reader_WorkbookGlobals', 0x5);
define('Spreadsheet_Excel_Reader_Worksheet', 0x10);

define('Spreadsheet_Excel_Reader_Type_BOF', 0x809);
define('Spreadsheet_Excel_Reader_Type_EOF', 0x0a);
define('Spreadsheet_Excel_Reader_Type_BOUNDSHEET', 0x85);
define('Spreadsheet_Excel_Reader_Type_DIMENSION', 0x200);
define('Spreadsheet_Excel_Reader_Type_ROW', 0x208);
define('Spreadsheet_Excel_Reader_Type_DBCELL', 0xd7);
define('Spreadsheet_Excel_Reader_Type_FILEPASS', 0x2f);
define('Spreadsheet_Excel_Reader_Type_NOTE', 0x1c);
define('Spreadsheet_Excel_Reader_Type_TXO', 0x1b6);
define('Spreadsheet_Excel_Reader_Type_RK', 0x7e);
define('Spreadsheet_Excel_Reader_Type_RK2', 0x27e);
define('Spreadsheet_Excel_Reader_Type_MULRK', 0xbd);
define('Spreadsheet_Excel_Reader_Type_MULBLANK', 0xbe);
define('Spreadsheet_Excel_Reader_Type_INDEX', 0x20b);
define('Spreadsheet_Excel_Reader_Type_SST', 0xfc);
define('Spreadsheet_Excel_Reader_Type_EXTSST', 0xff);
define('Spreadsheet_Excel_Reader_Type_CONTINUE', 0x3c);
define('Spreadsheet_Excel_Reader_Type_LABEL', 0x204);
define('Spreadsheet_Excel_Reader_Type_LABELSST', 0xfd);
define('Spreadsheet_Excel_Reader_Type_NUMBER', 0x203);
define('Spreadsheet_Excel_Reader_Type_NAME', 0x18);
define('Spreadsheet_Excel_Reader_Type_ARRAY', 0x221);
define('Spreadsheet_Excel_Reader_Type_STRING', 0x207);
define('Spreadsheet_Excel_Reader_Type_FORMULA', 0x406);
define('Spreadsheet_Excel_Reader_Type_FORMULA2', 0x6);
define('Spreadsheet_Excel_Reader_Type_FORMAT', 0x41e);
define('Spreadsheet_Excel_Reader_Type_XF', 0xe0);
define('Spreadsheet_Excel_Reader_Type_BOOLERR', 0x205);
define('Spreadsheet_Excel_Reader_Type_UNKNOWN', 0xffff);
define('Spreadsheet_Excel_Reader_Type_NINETEENFOUR', 0x22);
define('Spreadsheet_Excel_Reader_Type_MERGEDCELLS', 0xE5);

define('Spreadsheet_Excel_Reader_utcOffsetDays' , 25569);
define('Spreadsheet_Excel_Reader_utcOffsetDays1904', 24107);
define('Spreadsheet_Excel_Reader_msInADay', 24 * 60 * 60);

//define('Spreadsheet_Excel_Reader_DEF_NUM_FORMAT', "%.2f");
define('Spreadsheet_Excel_Reader_DEF_NUM_FORMAT', "%s");

// function file_get_contents for PHP < 4.3.0
// Thanks Marian Steinbach for this function
if (!function_exists('file_get_contents')) {
    function file_get_contents($filename, $use_include_path = 0) {
        $data = '';
        $file = @fopen($filename, "rb", $use_include_path);
        if ($file) {
            while (!feof($file)) $data .= fread($file, 1024);
            fclose($file);
        } else {
            // There was a problem opening the file
            $data = FALSE;
        }
        return $data;
    }
}


//class Spreadsheet_Excel_Reader extends PEAR {
class Spreadsheet_Excel_Reader {

    var $boundsheets = array();
    var $formatRecords = array();
    var $sst = array();
    var $sheets = array();
    var $data;
    var $pos;
    var $_ole;
    var $_defaultEncoding;
    var $_defaultFormat = Spreadsheet_Excel_Reader_DEF_NUM_FORMAT;
    var $_columnsFormat = array();
    var $_rowoffset = 1;
    var $_coloffset = 1;
    
    var $dateFormats = array (
        0xe => "d/m/Y",
        0xf => "d-M-Y",
        0x10 => "d-M",
        0x11 => "M-Y",
        0x12 => "h:i a",
        0x13 => "h:i:s a",
        0x14 => "H:i",
        0x15 => "H:i:s",
        0x16 => "d/m/Y H:i",
        0x2d => "i:s",
        0x2e => "H:i:s",
        0x2f => "i:s.S");

    var $numberFormats = array(
        0x1 => "%1.0f", // "0"
        0x2 => "%1.2f", // "0.00",
        0x3 => "%1.0f", //"#,##0",
        0x4 => "%1.2f", //"#,##0.00",
        0x5 => "%1.0f", /*"$#,##0;($#,##0)",*/
        0x6 => '$%1.0f', /*"$#,##0;($#,##0)",*/
        0x7 => '$%1.2f', //"$#,##0.00;($#,##0.00)",
        0x8 => '$%1.2f', //"$#,##0.00;($#,##0.00)",
        0x9 => '%1.0f%%', // "0%"
        0xa => '%1.2f%%', // "0.00%"
        0xb => '%1.2f', // 0.00E00",
        0x25 => '%1.0f', // "#,##0;(#,##0)",
        0x26 => '%1.0f', //"#,##0;(#,##0)",
        0x27 => '%1.2f', //"#,##0.00;(#,##0.00)",
        0x28 => '%1.2f', //"#,##0.00;(#,##0.00)",
        0x29 => '%1.0f', //"#,##0;(#,##0)",
        0x2a => '$%1.0f', //"$#,##0;($#,##0)",
        0x2b => '%1.2f', //"#,##0.00;(#,##0.00)",
        0x2c => '$%1.2f', //"$#,##0.00;($#,##0.00)",
        0x30 => '%1.0f'); //"##0.0E0";

    function Spreadsheet_Excel_Reader(){
        $this->_ole = new OLERead();
        $this->setUTFEncoder('iconv');

    }

    function setOutputEncoding($Encoding){
        $this->_defaultEncoding = $Encoding;
    }

    /**
    *  $encoder = 'iconv' or 'mb'
    *  set iconv if you would like use 'iconv' for encode UTF-16LE to your encoding
    *  set mb if you would like use 'mb_convert_encoding' for encode UTF-16LE to your encoding
    */
    function setUTFEncoder($encoder = 'iconv'){
    	$this->_encoderFunction = '';
    	if ($encoder == 'iconv'){
        	$this->_encoderFunction = function_exists('iconv') ? 'iconv' : '';
        }elseif ($encoder == 'mb') {
        	$this->_encoderFunction = function_exists('mb_convert_encoding') ? 'mb_convert_encoding' : '';
    	}
    }

    function setRowColOffset($iOffset){
        $this->_rowoffset = $iOffset;
		$this->_coloffset = $iOffset;
    }

    function setDefaultFormat($sFormat){
        $this->_defaultFormat = $sFormat;
    }

    function setColumnFormat($column, $sFormat){
        $this->_columnsFormat[$column] = $sFormat;
    }


    function read($sFileName) {
       $errlevel = error_reporting();
       $res = $this->_ole->read($sFileName); 
        
        // oops, something goes wrong (Darko Miljanovic)
        if($res == false) {
        	// check error code
        	if($this->_ole->error == 1) {
        	// bad file
			
        		die('The filename ' . $sFileName . ' is not readable');	
        	}
        	// check other error codes here (eg bad fileformat, etc...)
        }

        $this->data = $this->_ole->getWorkBook();

        
        /*
        $res = $this->_ole->read($sFileName);

        if ($this->isError($res)) {
//		var_dump($res);		
            return $this->raiseError($res);
        }

        $total = $this->_ole->ppsTotal();
        for ($i = 0; $i < $total; $i++) {
            if ($this->_ole->isFile($i)) {
                $type = unpack("v", $this->_ole->getData($i, 0, 2));
                if ($type[''] == 0x0809)  { // check if it's a BIFF stream
                    $this->_index = $i;
                    $this->data = $this->_ole->getData($i, 0, $this->_ole->getDataLength($i));
                    break;
                }
            }
        }

        if ($this->_index === null) {
            return $this->raiseError("$file doesn't seem to be an Excel file");
        }
        
        */
		
		//var_dump($this->data);
	//echo "data =".$this->data;	
        $this->pos = 0;
        //$this->readRecords();
        $this->_parse();
    	error_reporting($errlevel);

    }

    function _parse(){
        $pos = 0;

        $code = ord($this->data[$pos]) | ord($this->data[$pos+1])<<8;
        $length = ord($this->data[$pos+2]) | ord($this->data[$pos+3])<<8;

        $version = ord($this->data[$pos + 4]) | ord($this->data[$pos + 5])<<8;
        $substreamType = ord($this->data[$pos + 6]) | ord($this->data[$pos + 7])<<8;
        //echo "Start parse code=".base_convert($code,10,16)." version=".base_convert($version,10,16)." substreamType=".base_convert($substreamType,10,16).""."\n";

        if (($version != Spreadsheet_Excel_Reader_BIFF8) && ($version != Spreadsheet_Excel_Reader_BIFF7)) {
            return false;
        }

        if ($substreamType != Spreadsheet_Excel_Reader_WorkbookGlobals){
            return false;
        }

        //print_r($rec);
        $pos += $length + 4;

        $code = ord($this->data[$pos]) | ord($this->data[$pos+1])<<8;
        $length = ord($this->data[$pos+2]) | ord($this->data[$pos+3])<<8;

        while ($code != Spreadsheet_Excel_Reader_Type_EOF){
            switch ($code) {
                case Spreadsheet_Excel_Reader_Type_SST:
                    //echo "Type_SST\n";
                     $spos = $pos + 4;
                     $limitpos = $spos + $length;
                     $uniqueStrings = $this->_GetInt4d($this->data, $spos+4);
                                                $spos += 8;
                                       for ($i = 0; $i < $uniqueStrings; $i++) {
        // Read in the number of characters
                                                if ($spos == $limitpos) {
                                                $opcode = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                                                $conlength = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
                                                        if ($opcode != 0x3c) {
                                                                return -1;
                                                        }
                                                $spos += 4;
                                                $limitpos = $spos + $conlength;
                                                }
                                                $numChars = ord($this->data[$spos]) | (ord($this->data[$spos+1]) << 8);
                                                //echo "i = $i pos = $pos numChars = $numChars ";
                                                $spos += 2;
                                                $optionFlags = ord($this->data[$spos]);
                                                $spos++;
                                        $asciiEncoding = (($optionFlags & 0x01) == 0) ;
                                                $extendedString = ( ($optionFlags & 0x04) != 0);

                                                // See if string contains formatting information
                                                $richString = ( ($optionFlags & 0x08) != 0);

                                                if ($richString) {
                                        // Read in the crun
                                                        $formattingRuns = ord($this->data[$spos]) | (ord($this->data[$spos+1]) << 8);
                                                        $spos += 2;
                                                }

                                                if ($extendedString) {
                                                  // Read in cchExtRst
                                                  $extendedRunLength = $this->_GetInt4d($this->data, $spos);
                                                  $spos += 4;
                                                }

                                                $len = ($asciiEncoding)? $numChars : $numChars*2;
                                                if ($spos + $len < $limitpos) {
                                                                $retstr = substr($this->data, $spos, $len);
                                                                $spos += $len;
                                                }else{
                                                        // found countinue
                                                        $retstr = substr($this->data, $spos, $limitpos - $spos);
                                                        $bytesRead = $limitpos - $spos;
                                                        $charsLeft = $numChars - (($asciiEncoding) ? $bytesRead : ($bytesRead / 2));
                                                        $spos = $limitpos;

                                                         while ($charsLeft > 0){
                                                                $opcode = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                                                                $conlength = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
                                                                        if ($opcode != 0x3c) {
                                                                                return -1;
                                                                        }
                                                                $spos += 4;
                                                                $limitpos = $spos + $conlength;
                                                                $option = ord($this->data[$spos]);
                                                                $spos += 1;
                                                                  if ($asciiEncoding && ($option == 0)) {
                                                                                $len = min($charsLeft, $limitpos - $spos); // min($charsLeft, $conlength);
                                                                    $retstr .= substr($this->data, $spos, $len);
                                                                    $charsLeft -= $len;
                                                                    $asciiEncoding = true;
                                                                  }elseif (!$asciiEncoding && ($option != 0)){
                                                                                $len = min($charsLeft * 2, $limitpos - $spos); // min($charsLeft, $conlength);
                                                                    $retstr .= substr($this->data, $spos, $len);
                                                                    $charsLeft -= $len/2;
                                                                    $asciiEncoding = false;
                                                                  }elseif (!$asciiEncoding && ($option == 0)) {
                                                                // Bummer - the string starts off as Unicode, but after the
                                                                // continuation it is in straightforward ASCII encoding
                                                                                $len = min($charsLeft, $limitpos - $spos); // min($charsLeft, $conlength);
                                                                        for ($j = 0; $j < $len; $j++) {
                                                                 $retstr .= $this->data[$spos + $j].chr(0);
                                                                }
                                                            $charsLeft -= $len;
                                                                $asciiEncoding = false;
                                                                  }else{
                                                            $newstr = '';
                                                                    for ($j = 0; $j < strlen($retstr); $j++) {
                                                                      $newstr = $retstr[$j].chr(0);
                                                                    }
                                                                    $retstr = $newstr;
                                                                                $len = min($charsLeft * 2, $limitpos - $spos); // min($charsLeft, $conlength);
                                                                    $retstr .= substr($this->data, $spos, $len);
                                                                    $charsLeft -= $len/2;
                                                                    $asciiEncoding = false;
                                                                        //echo "Izavrat\n";
                                                                  }
                                                          $spos += $len;

                                                         }
                                                }
                                                $retstr = ($asciiEncoding) ? $retstr : $this->_encodeUTF16($retstr);
//                                              echo "Str $i = $retstr\n";
                                        if ($richString){
                                                  $spos += 4 * $formattingRuns;
                                                }

                                                // For extended strings, skip over the extended string data
                                                if ($extendedString) {
                                                  $spos += $extendedRunLength;
                                                }
                                                        //if ($retstr == 'Derby'){
                                                        //      echo "bb\n";
                                                        //}
                                                $this->sst[]=$retstr;
                                       }
                    /*$continueRecords = array();
                    while ($this->getNextCode() == Type_CONTINUE) {
                        $continueRecords[] = &$this->nextRecord();
                    }
                    //echo " 1 Type_SST\n";
                    $this->shareStrings = new SSTRecord($r, $continueRecords);
                    //print_r($this->shareStrings->strings);
                     */
                     // echo 'SST read: '.($time_end-$time_start)."\n";
                    break;

                case Spreadsheet_Excel_Reader_Type_FILEPASS:
                    return false;
                    break;
                case Spreadsheet_Excel_Reader_Type_NAME:
                    //echo "Type_NAME\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_FORMAT:
                        $indexCode = ord($this->data[$pos+4]) | ord($this->data[$pos+5]) << 8;

                        if ($version == Spreadsheet_Excel_Reader_BIFF8) {
                            $numchars = ord($this->data[$pos+6]) | ord($this->data[$pos+7]) << 8;
                            if (ord($this->data[$pos+8]) == 0){
                                $formatString = substr($this->data, $pos+9, $numchars);
                            } else {
                                $formatString = substr($this->data, $pos+9, $numchars*2);
                            }
                        } else {
                            $numchars = ord($this->data[$pos+6]);
                            $formatString = substr($this->data, $pos+7, $numchars*2);
                        }

                    $this->formatRecords[$indexCode] = $formatString;
                   // echo "Type.FORMAT\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_XF:
                        //global $dateFormats, $numberFormats;
                        $indexCode = ord($this->data[$pos+6]) | ord($this->data[$pos+7]) << 8;
                        //echo "\nType.XF ".count($this->formatRecords['xfrecords'])." $indexCode ";
                        if (array_key_exists($indexCode, $this->dateFormats)) {
                            //echo "isdate ".$dateFormats[$indexCode];
                            $this->formatRecords['xfrecords'][] = array(
                                    'type' => 'date',
                                    'format' => $this->dateFormats[$indexCode]
                                    );
                        }elseif (array_key_exists($indexCode, $this->numberFormats)) {
                        //echo "isnumber ".$this->numberFormats[$indexCode];
                            $this->formatRecords['xfrecords'][] = array(
                                    'type' => 'number',
                                    'format' => $this->numberFormats[$indexCode]
                                    );
                        }else{
                            $isdate = FALSE;
                            if ($indexCode > 0){
                            	if (isset($this->formatRecords[$indexCode]))
                                	$formatstr = $this->formatRecords[$indexCode];
                                //echo '.other.';
                                //echo "\ndate-time=$formatstr=\n";
                                if ($formatstr)
                                if (preg_match("/[^hmsday\/\-:\s]/i", $formatstr) == 0) { // found day and time format
                                    $isdate = TRUE;
                                    $formatstr = str_replace('mm', 'i', $formatstr);
                                    $formatstr = str_replace('h', 'H', $formatstr);
                                    //echo "\ndate-time $formatstr \n";
                                }
                            }

                            if ($isdate){
                                $this->formatRecords['xfrecords'][] = array(
                                        'type' => 'date',
                                        'format' => $formatstr,
                                        );
                            }else{
                                $this->formatRecords['xfrecords'][] = array(
                                        'type' => 'other',
                                        'format' => '',
                                        'code' => $indexCode
                                        );
                            }
                        }
                        //echo "\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_NINETEENFOUR:
                    //echo "Type.NINETEENFOUR\n";
                    $this->nineteenFour = (ord($this->data[$pos+4]) == 1);
                    break;
                case Spreadsheet_Excel_Reader_Type_BOUNDSHEET:
                    //echo "Type.BOUNDSHEET\n";
                        $rec_offset = $this->_GetInt4d($this->data, $pos+4);
                        $rec_typeFlag = ord($this->data[$pos+8]);
                        $rec_visibilityFlag = ord($this->data[$pos+9]);
                        $rec_length = ord($this->data[$pos+10]);

                        if ($version == Spreadsheet_Excel_Reader_BIFF8){
                            $chartype =  ord($this->data[$pos+11]);
                            if ($chartype == 0){
                                $rec_name    = substr($this->data, $pos+12, $rec_length);
                            } else {
                                $rec_name    = $this->_encodeUTF16(substr($this->data, $pos+12, $rec_length*2));
                            }
                        }elseif ($version == Spreadsheet_Excel_Reader_BIFF7){
                                $rec_name    = substr($this->data, $pos+11, $rec_length);
                        }
                    $this->boundsheets[] = array('name'=>$rec_name,
                                                 'offset'=>$rec_offset);

                    break;

            }

            //echo "Code = ".base_convert($r['code'],10,16)."\n";
            $pos += $length + 4;
            $code = ord($this->data[$pos]) | ord($this->data[$pos+1])<<8;
            $length = ord($this->data[$pos+2]) | ord($this->data[$pos+3])<<8;

            //$r = &$this->nextRecord();
            //echo "1 Code = ".base_convert($r['code'],10,16)."\n";
        }

        foreach ($this->boundsheets as $key=>$val){
            $this->sn = $key;
            $this->_parsesheet($val['offset']);
        }
        return true;

    }

    function _parsesheet($spos){
        $cont = true;
        // read BOF
        $code = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
        $length = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;

        $version = ord($this->data[$spos + 4]) | ord($this->data[$spos + 5])<<8;
        $substreamType = ord($this->data[$spos + 6]) | ord($this->data[$spos + 7])<<8;

        if (($version != Spreadsheet_Excel_Reader_BIFF8) && ($version != Spreadsheet_Excel_Reader_BIFF7)) {
            return -1;
        }

        if ($substreamType != Spreadsheet_Excel_Reader_Worksheet){
            return -2;
        }
        //echo "Start parse code=".base_convert($code,10,16)." version=".base_convert($version,10,16)." substreamType=".base_convert($substreamType,10,16).""."\n";
        $spos += $length + 4;
        //var_dump($this->formatRecords);
	//echo "code $code $length";
        while($cont) {
            //echo "mem= ".memory_get_usage()."\n";
//            $r = &$this->file->nextRecord();
            $lowcode = ord($this->data[$spos]);
            if ($lowcode == Spreadsheet_Excel_Reader_Type_EOF) break;
            $code = $lowcode | ord($this->data[$spos+1])<<8;
            $length = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
            $spos += 4;
            $this->sheets[$this->sn]['maxrow'] = $this->_rowoffset - 1;
            $this->sheets[$this->sn]['maxcol'] = $this->_coloffset - 1;
            //echo "Code=".base_convert($code,10,16)." $code\n";
            unset($this->rectype);
            $this->multiplier = 1; // need for format with %
            switch ($code) {
                case Spreadsheet_Excel_Reader_Type_DIMENSION:
                    //echo 'Type_DIMENSION ';
                    if (!isset($this->numRows)) {
                        if (($length == 10) ||  ($version == Spreadsheet_Excel_Reader_BIFF7)){
                            $this->sheets[$this->sn]['numRows'] = ord($this->data[$spos+2]) | ord($this->data[$spos+3]) << 8;
                            $this->sheets[$this->sn]['numCols'] = ord($this->data[$spos+6]) | ord($this->data[$spos+7]) << 8;
                        } else {
                            $this->sheets[$this->sn]['numRows'] = ord($this->data[$spos+4]) | ord($this->data[$spos+5]) << 8;
                            $this->sheets[$this->sn]['numCols'] = ord($this->data[$spos+10]) | ord($this->data[$spos+11]) << 8;
                        }
                    }
                    //echo 'numRows '.$this->numRows.' '.$this->numCols."\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_MERGEDCELLS:
                    $cellRanges = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                    for ($i = 0; $i < $cellRanges; $i++) {
                        $fr =  ord($this->data[$spos + 8*$i + 2]) | ord($this->data[$spos + 8*$i + 3])<<8;
                        $lr =  ord($this->data[$spos + 8*$i + 4]) | ord($this->data[$spos + 8*$i + 5])<<8;
                        $fc =  ord($this->data[$spos + 8*$i + 6]) | ord($this->data[$spos + 8*$i + 7])<<8;
                        $lc =  ord($this->data[$spos + 8*$i + 8]) | ord($this->data[$spos + 8*$i + 9])<<8;
                        //$this->sheets[$this->sn]['mergedCells'][] = array($fr + 1, $fc + 1, $lr + 1, $lc + 1);
                        if ($lr - $fr > 0) {
                            $this->sheets[$this->sn]['cellsInfo'][$fr+1][$fc+1]['rowspan'] = $lr - $fr + 1;
                        }
                        if ($lc - $fc > 0) {
                            $this->sheets[$this->sn]['cellsInfo'][$fr+1][$fc+1]['colspan'] = $lc - $fc + 1;
                        }
                    }
                    //echo "Merged Cells $cellRanges $lr $fr $lc $fc\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_RK:
                case Spreadsheet_Excel_Reader_Type_RK2:
                    //echo 'Spreadsheet_Excel_Reader_Type_RK'."\n";
                    $row = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                    $column = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
                    $rknum = $this->_GetInt4d($this->data, $spos + 6);
                    $numValue = $this->_GetIEEE754($rknum);
                    //echo $numValue." ";
                    if ($this->isDate($spos)) {
                        list($string, $raw) = $this->createDate($numValue);
                    }else{
                        $raw = $numValue;
                        if (isset($this->_columnsFormat[$column + 1])){
                                $this->curformat = $this->_columnsFormat[$column + 1];
                        }
                        $string = sprintf($this->curformat, $numValue * $this->multiplier);
                        //$this->addcell(RKRecord($r));
                    }
                    $this->addcell($row, $column, $string, $raw);
                    //echo "Type_RK $row $column $string $raw {$this->curformat}\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_LABELSST:
                        $row        = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                        $column     = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
                        $xfindex    = ord($this->data[$spos+4]) | ord($this->data[$spos+5])<<8;
                        $index  = $this->_GetInt4d($this->data, $spos + 6);
			//var_dump($this->sst);
                        $this->addcell($row, $column, $this->sst[$index]);
                        //echo "LabelSST $row $column $string\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_MULRK:
                    $row        = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                    $colFirst   = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
                    $colLast    = ord($this->data[$spos + $length - 2]) | ord($this->data[$spos + $length - 1])<<8;
                    $columns    = $colLast - $colFirst + 1;
                    $tmppos = $spos+4;
                    for ($i = 0; $i < $columns; $i++) {
                        $numValue = $this->_GetIEEE754($this->_GetInt4d($this->data, $tmppos + 2));
                        if ($this->isDate($tmppos-4)) {
                            list($string, $raw) = $this->createDate($numValue);
                        }else{
                            $raw = $numValue;
                            if (isset($this->_columnsFormat[$colFirst + $i + 1])){
                                        $this->curformat = $this->_columnsFormat[$colFirst + $i + 1];
                                }
                            $string = sprintf($this->curformat, $numValue * $this->multiplier);
                        }
                      //$rec['rknumbers'][$i]['xfindex'] = ord($rec['data'][$pos]) | ord($rec['data'][$pos+1]) << 8;
                      $tmppos += 6;
                      $this->addcell($row, $colFirst + $i, $string, $raw);
                      //echo "MULRK $row ".($colFirst + $i)." $string\n";
                    }
                     //MulRKRecord($r);
                    // Get the individual cell records from the multiple record
                     //$num = ;

                    break;
                case Spreadsheet_Excel_Reader_Type_NUMBER:
                    $row    = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                    $column = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
                    $tmp = unpack("ddouble", substr($this->data, $spos + 6, 8)); // It machine machine dependent
                    if ($this->isDate($spos)) {
                        list($string, $raw) = $this->createDate($tmp['double']);
                     //   $this->addcell(DateRecord($r, 1));
                    }else{
                        //$raw = $tmp[''];
                        if (isset($this->_columnsFormat[$column + 1])){
                                $this->curformat = $this->_columnsFormat[$column + 1];
                        }
                        $raw = $this->createNumber($spos);
                        $string = sprintf($this->curformat, $raw * $this->multiplier);

                     //   $this->addcell(NumberRecord($r));
                    }
                    $this->addcell($row, $column, $string, $raw);
                    //echo "Number $row $column $string\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_FORMULA:
                case Spreadsheet_Excel_Reader_Type_FORMULA2:
                    $row    = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                    $column = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
					if ((ord($this->data[$spos+6])==0) && (ord($this->data[$spos+12])==255) && (ord($this->data[$spos+13])==255)) {
						//String formula. Result follows in a STRING record
					    //echo "FORMULA $row $column Formula with a string<br>\n";
					} elseif ((ord($this->data[$spos+6])==1) && (ord($this->data[$spos+12])==255) && (ord($this->data[$spos+13])==255)) {
						//Boolean formula. Result is in +2; 0=false,1=true
					} elseif ((ord($this->data[$spos+6])==2) && (ord($this->data[$spos+12])==255) && (ord($this->data[$spos+13])==255)) {
						//Error formula. Error code is in +2;
					} elseif ((ord($this->data[$spos+6])==3) && (ord($this->data[$spos+12])==255) && (ord($this->data[$spos+13])==255)) {
						//Formula result is a null string.
					} else {
						// result is a number, so first 14 bytes are just like a _NUMBER record
	                    $tmp = unpack("ddouble", substr($this->data, $spos + 6, 8)); // It machine machine dependent
	                    if ($this->isDate($spos)) {
	                        list($string, $raw) = $this->createDate($tmp['double']);
	                     //   $this->addcell(DateRecord($r, 1));
	                    }else{
	                        //$raw = $tmp[''];
	                        if (isset($this->_columnsFormat[$column + 1])){
	                                $this->curformat = $this->_columnsFormat[$column + 1];
	                        }
	                        $raw = $this->createNumber($spos);
							$string = sprintf($this->curformat, $raw * $this->multiplier);
	
	                     //   $this->addcell(NumberRecord($r));
	                    }
	                    $this->addcell($row, $column, $string, $raw);
	                    //echo "Number $row $column $string\n";
					}
					break;                    
                case Spreadsheet_Excel_Reader_Type_BOOLERR:
                    $row    = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                    $column = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
                    $string = ord($this->data[$spos+6]);
                    $this->addcell($row, $column, $string);
                    //echo 'Type_BOOLERR '."\n";
                    break;
                case Spreadsheet_Excel_Reader_Type_ROW:
                case Spreadsheet_Excel_Reader_Type_DBCELL:
                case Spreadsheet_Excel_Reader_Type_MULBLANK:
                    break;
                case Spreadsheet_Excel_Reader_Type_LABEL:
                    $row    = ord($this->data[$spos]) | ord($this->data[$spos+1])<<8;
                    $column = ord($this->data[$spos+2]) | ord($this->data[$spos+3])<<8;
                    $this->addcell($row, $column, substr($this->data, $spos + 8, ord($this->data[$spos + 6]) | ord($this->data[$spos + 7])<<8));

                   // $this->addcell(LabelRecord($r));
                    break;

                case Spreadsheet_Excel_Reader_Type_EOF:
                    $cont = false;
                    break;
                default:
                    //echo ' unknown :'.base_convert($r['code'],10,16)."\n";
                    break;

            }
            $spos += $length;
        }

        if (!isset($this->sheets[$this->sn]['numRows']))
        	 $this->sheets[$this->sn]['numRows'] = $this->sheets[$this->sn]['maxrow'];
        if (!isset($this->sheets[$this->sn]['numCols']))
        	 $this->sheets[$this->sn]['numCols'] = $this->sheets[$this->sn]['maxcol'];

    }

    function isDate($spos){
        //$xfindex = GetInt2d(, 4);
        $xfindex = ord($this->data[$spos+4]) | ord($this->data[$spos+5]) << 8;
        //echo 'check is date '.$xfindex.' '.$this->formatRecords['xfrecords'][$xfindex]['type']."\n";
        //var_dump($this->formatRecords['xfrecords'][$xfindex]);
        if ($this->formatRecords['xfrecords'][$xfindex]['type'] == 'date') {
            $this->curformat = $this->formatRecords['xfrecords'][$xfindex]['format'];
            $this->rectype = 'date';
            return true;
        } else {
            if ($this->formatRecords['xfrecords'][$xfindex]['type'] == 'number') {
                $this->curformat = $this->formatRecords['xfrecords'][$xfindex]['format'];
                $this->rectype = 'number';
                if (($xfindex == 0x9) || ($xfindex == 0xa)){
                    $this->multiplier = 100;
                }
            }else{
                $this->curformat = $this->_defaultFormat;
                $this->rectype = 'unknown';
            }
            return false;
        }
    }

    function createDate($numValue){
        if ($numValue > 1){
            $utcDays = $numValue - ($this->nineteenFour ? Spreadsheet_Excel_Reader_utcOffsetDays1904 : Spreadsheet_Excel_Reader_utcOffsetDays);
            $utcValue = round($utcDays * Spreadsheet_Excel_Reader_msInADay);
            $string = date ($this->curformat, $utcValue);
            $raw = $utcValue;
        }else{
            $raw = $numValue;
            $hours = floor($numValue * 24);
            $mins = floor($numValue * 24 * 60) - $hours * 60;
            $secs = floor($numValue * Spreadsheet_Excel_Reader_msInADay) - $hours * 60 * 60 - $mins * 60;
            $string = date ($this->curformat, mktime($hours, $mins, $secs));
        }
        return array($string, $raw);
    }

    function createNumber($spos){
		$rknumhigh = $this->_GetInt4d($this->data, $spos + 10);
		$rknumlow = $this->_GetInt4d($this->data, $spos + 6);
		//for ($i=0; $i<8; $i++) { echo ord($this->data[$i+$spos+6]) . " "; } echo "<br>";
		$sign = ($rknumhigh & 0x80000000) >> 31;
		$exp =  ($rknumhigh & 0x7ff00000) >> 20;
		$mantissa = (0x100000 | ($rknumhigh & 0x000fffff));
		$mantissalow1 = ($rknumlow & 0x80000000) >> 31;
		$mantissalow2 = ($rknumlow & 0x7fffffff);
		$value = $mantissa / pow( 2 , (20- ($exp - 1023)));
		if ($mantissalow1 != 0) $value += 1 / pow (2 , (21 - ($exp - 1023)));
		$value += $mantissalow2 / pow (2 , (52 - ($exp - 1023)));
		//echo "Sign = $sign, Exp = $exp, mantissahighx = $mantissa, mantissalow1 = $mantissalow1, mantissalow2 = $mantissalow2<br>\n";
		if ($sign) {$value = -1 * $value;}
		return  $value;
    }

    function addcell($row, $col, $string, $raw = ''){
        //echo "ADD cel $row-$col $string\n";
        $this->sheets[$this->sn]['maxrow'] = max($this->sheets[$this->sn]['maxrow'], $row + $this->_rowoffset);
        $this->sheets[$this->sn]['maxcol'] = max($this->sheets[$this->sn]['maxcol'], $col + $this->_coloffset);
        $this->sheets[$this->sn]['cells'][$row + $this->_rowoffset][$col + $this->_coloffset] = $string;
        if ($raw)
            $this->sheets[$this->sn]['cellsInfo'][$row + $this->_rowoffset][$col + $this->_coloffset]['raw'] = $raw;
        if (isset($this->rectype))
            $this->sheets[$this->sn]['cellsInfo'][$row + $this->_rowoffset][$col + $this->_coloffset]['type'] = $this->rectype;

    }


    function _GetIEEE754($rknum){
        if (($rknum & 0x02) != 0) {
                $value = $rknum >> 2;
        } else {
//mmp
// first comment out the previously existing 7 lines of code here
//                $tmp = unpack("d", pack("VV", 0, ($rknum & 0xfffffffc)));
//                //$value = $tmp[''];
//                if (array_key_exists(1, $tmp)) {
//                    $value = $tmp[1];
//                } else {
//                    $value = $tmp[''];
//                }
// I got my info on IEEE754 encoding from 
// http://research.microsoft.com/~hollasch/cgindex/coding/ieeefloat.html
// The RK format calls for using only the most significant 30 bits of the
// 64 bit floating point value. The other 34 bits are assumed to be 0
// So, we use the upper 30 bits of $rknum as follows...
 		$sign = ($rknum & 0x80000000) >> 31;
		$exp = ($rknum & 0x7ff00000) >> 20;
		$mantissa = (0x100000 | ($rknum & 0x000ffffc));
		$value = $mantissa / pow( 2 , (20- ($exp - 1023)));
		if ($sign) {$value = -1 * $value;}
//end of changes by mmp		

        }

        if (($rknum & 0x01) != 0) {
            $value /= 100;
        }
        return $value;
    }

    function _encodeUTF16($string){
    	$result = $string;
        if ($this->_defaultEncoding){
        	switch ($this->_encoderFunction){
        		case 'iconv' : 	$result = iconv('UTF-16LE', $this->_defaultEncoding, $string);
        						break;
        		case 'mb_convert_encoding' : 	$result = mb_convert_encoding($string, $this->_defaultEncoding, 'UTF-16LE' );
        						break;
        	}
        }
        return $result;
    }

    function _GetInt4d($data, $pos) {
        return ord($data[$pos]) | (ord($data[$pos+1]) << 8) | (ord($data[$pos+2]) << 16) | (ord($data[$pos+3]) << 24);
    }

}

class SimpleXLSX {
	// Don't remove this string! Created by Sergey Schuchkin from http://www.sibvision.ru - professional php developers team 2010-2012
	private $workbook;
	private $sheets;
	private $hyperlinks;
	private $package = array(
		'filename' => '',
		'mtime' => 0,
		'size' => 0,
		'comment' => '',
		'entries' => array()
	);
	private $sharedstrings;
	private $error = false;
	// scheme
	const SCHEMA_OFFICEDOCUMENT  =  'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';
	const SCHEMA_RELATIONSHIP  =  'http://schemas.openxmlformats.org/package/2006/relationships';
	const SCHEMA_SHAREDSTRINGS =  'http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings';
	const SCHEMA_WORKSHEETRELATION =  'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet';
	
	function __construct( $filename, $is_data = false ) {
		$this->_unzip( $filename, $is_data );
		$this->_parse();
	}
	function sheets() {
		return $this->sheets;
	}
	function sheetsCount() {
		return count($this->sheets);
	}
	function sheetName( $id ) {

		foreach( $this->workbook->sheets->sheet as $s ) {
			
			if ( $s->attributes('r',true)->id == 'rId'.$id)
				return $s['name'];

		}
		return false;
	}
	function worksheet( $worksheet_id ) {
		if ( isset( $this->sheets[ $worksheet_id ] ) ) {
			$ws = $this->sheets[ $worksheet_id ];
			
			if (isset($ws->hyperlinks)) {
				$this->hyperlinks = array();
				foreach( $ws->hyperlinks->hyperlink as $hyperlink ) {
					$this->hyperlinks[ (string) $hyperlink['ref'] ] = (string) $hyperlink['display'];
				}
			}
			
			return $ws;
		} else {
			$this->error( 'Worksheet '.$worksheet_id.' not found.' );
			return false;
		}
	}
	function dimension( $worksheet_id = 1 ) {
		
		if (($ws = $this->worksheet( $worksheet_id)) === false)
			return false;
		
		$ref = (string) $ws->dimension['ref'];
		$d = explode(':', $ref);
		$index = $this->_columnIndex( $d[1] );		
		return array( $index[0]+1, $index[1]+1);
	}
	// sheets numeration: 1,2,3....
	function rows( $worksheet_id = 1 ) {
		
		if (($ws = $this->worksheet( $worksheet_id)) === false)
			return false;
		
		$rows = array();
		$curR = 0;
		
		list($cols,) = $this->dimension( $worksheet_id );
				
		foreach ($ws->sheetData->row as $row) {
			
			foreach ($row->c as $c) {
				list($curC,) = $this->_columnIndex((string) $c['r']);
				$rows[ $curR ][ $curC ] = $this->value($c);
			}
			for ($i = 0; $i < $cols; $i++)
				if (!isset($rows[$curR][$i]))
					$rows[ $curR ][ $i ] = '';

			ksort( $rows[ $curR ] );
			
			$curR++;
		}
		return $rows;
	}
	function rowsEx( $worksheet_id = 1 ) {
		
		if (($ws = $this->worksheet( $worksheet_id)) === false)
			return false;
		
		$rows = array();
		$curR = 0;
		list($cols,) = $this->dimension( $worksheet_id );
		
		foreach ($ws->sheetData->row as $row) {
			
			foreach ($row->c as $c) {
				list($curC,) = $this->_columnIndex((string) $c['r']);
				
				$rows[ $curR ][ $curC ] = array(
					'type' => (string)$c['t'],
					'name' => (string) $c['r'],
					'value' => $this->value($c),
					'href' => $this->href( $c ),
					'f' => (string) $c['f']
				);
			}
			for ($i = 0; $i < $cols; $i++)
				if (!isset($rows[$curR][$i]))
					$rows[ $curR ][$i] = array(
						'type' => '',
						'name' => chr($i + 65).($curR+1),
						'value' => '',
						'href' => '',
						'f' => ''
					);
					
			ksort( $rows[ $curR ] );
			
			$curR++;
		}
		return $rows;

	}
	// thx Gonzo
	function _columnIndex( $cell = 'A1' ) {
		
		if (preg_match("/([A-Z]+)(\d+)/", $cell, $matches)) {
			
			$col = $matches[1];
			$row = $matches[2];
			
			$colLen = strlen($col);
			$index = 0;

			for ($i = $colLen-1; $i >= 0; $i--)
				$index += (ord($col{$i}) - 64) * pow(26, $colLen-$i-1);

			return array($index-1, $row-1);
		} else
			throw new Exception("Invalid cell index.");
	}
	function value( $cell ) {
		// Determine data type
		$dataType = (string)$cell['t'];
		switch ($dataType) {
			case "s":
				// Value is a shared string
				if ((string)$cell->v != '') {
					$value = $this->sharedstrings[intval($cell->v)];
				} else {
					$value = '';
				}

				break;
				
			case "b":
				// Value is boolean
				$value = (string)$cell->v;
				if ($value == '0') {
					$value = false;
				} else if ($value == '1') {
					$value = true;
				} else {
					$value = (bool)$cell->v;
				}

				break;
				
			case "inlineStr":
				// Value is rich text inline
				$value = $this->_parseRichText($cell->is);
							
				break;
				
			case "e":
				// Value is an error message
				if ((string)$cell->v != '') {
					$value = (string)$cell->v;
				} else {
					$value = '';
				}

				break;

			default:
				// Value is a string
				$value = (string)$cell->v;

				// Check for numeric values
				if (is_numeric($value) && $dataType != 's') {
					if ($value == (int)$value) $value = (int)$value;
					elseif ($value == (float)$value) $value = (float)$value;
					elseif ($value == (double)$value) $value = (double)$value;
				}
		}
		return $value;
	}
	function href( $cell ) {
		return isset( $this->hyperlinks[ (string) $cell['r'] ] ) ? $this->hyperlinks[ (string) $cell['r'] ] : '';
	}
	function _unzip( $filename, $is_data = false ) {
		
		// Clear current file
		$this->datasec = array();
			
		if ($is_data) {

			$this->package['filename'] = 'default.xlsx';
			$this->package['mtime'] = time();
			$this->package['size'] = strlen( $filename );
			
			$vZ = $filename;
		} else {
			
			if (!is_readable($filename)) {
				$this->error( 'File not found' );
				return false;
			}
			
			// Package information
			$this->package['filename'] = $filename;
			$this->package['mtime'] = filemtime( $filename );
			$this->package['size'] = filesize( $filename );

			// Read file
			$oF = fopen($filename, 'rb');
			$vZ = fread($oF, $this->package['size']);
			fclose($oF);

		}
		// Cut end of central directory
		$aE = explode("\x50\x4b\x05\x06", $vZ);
		
		if (count($aE) == 1) {
			$this->error('Unknown format');
			return false;
		}

		// Normal way
		$aP = unpack('x16/v1CL', $aE[1]);
		$this->package['comment'] = substr($aE[1], 18, $aP['CL']);

		// Translates end of line from other operating systems
		$this->package['comment'] = strtr($this->package['comment'], array("\r\n" => "\n", "\r" => "\n"));

		// Cut the entries from the central directory
		$aE = explode("\x50\x4b\x01\x02", $vZ);
		// Explode to each part
		$aE = explode("\x50\x4b\x03\x04", $aE[0]);
		// Shift out spanning signature or empty entry
		array_shift($aE);

		// Loop through the entries
		foreach ($aE as $vZ) {
			$aI = array();
			$aI['E']  = 0;
			$aI['EM'] = '';
			// Retrieving local file header information
//			$aP = unpack('v1VN/v1GPF/v1CM/v1FT/v1FD/V1CRC/V1CS/V1UCS/v1FNL', $vZ);
			$aP = unpack('v1VN/v1GPF/v1CM/v1FT/v1FD/V1CRC/V1CS/V1UCS/v1FNL/v1EFL', $vZ);
			// Check if data is encrypted
//			$bE = ($aP['GPF'] && 0x0001) ? TRUE : FALSE;
			$bE = false;
			$nF = $aP['FNL'];
			$mF = $aP['EFL'];

			// Special case : value block after the compressed data
			if ($aP['GPF'] & 0x0008) {
				$aP1 = unpack('V1CRC/V1CS/V1UCS', substr($vZ, -12));

				$aP['CRC'] = $aP1['CRC'];
				$aP['CS']  = $aP1['CS'];
				$aP['UCS'] = $aP1['UCS'];

				$vZ = substr($vZ, 0, -12);
			}

			// Getting stored filename
			$aI['N'] = substr($vZ, 26, $nF);

			if (substr($aI['N'], -1) == '/') {
				// is a directory entry - will be skipped
				continue;
			}

			// Truncate full filename in path and filename
			$aI['P'] = dirname($aI['N']);
			$aI['P'] = $aI['P'] == '.' ? '' : $aI['P'];
			$aI['N'] = basename($aI['N']);

			$vZ = substr($vZ, 26 + $nF + $mF);

			if (strlen($vZ) != $aP['CS']) {
			  $aI['E']  = 1;
			  $aI['EM'] = 'Compressed size is not equal with the value in header information.';
			} else {
				if ($bE) {
					$aI['E']  = 5;
					$aI['EM'] = 'File is encrypted, which is not supported from this class.';
				} else {
					switch($aP['CM']) {
						case 0: // Stored
							// Here is nothing to do, the file ist flat.
							break;
						case 8: // Deflated
							$vZ = gzinflate($vZ);
							break;
						case 12: // BZIP2
							if (! extension_loaded('bz2')) {
								if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
								  @dl('php_bz2.dll');
								} else {
								  @dl('bz2.so');
								}
							}
							if (extension_loaded('bz2')) {
								$vZ = bzdecompress($vZ);
							} else {
								$aI['E']  = 7;
								$aI['EM'] = "PHP BZIP2 extension not available.";
							}
							break;
						default:
						  $aI['E']  = 6;
						  $aI['EM'] = "De-/Compression method {$aP['CM']} is not supported.";
					}
					if (! $aI['E']) {
						if ($vZ === FALSE) {
							$aI['E']  = 2;
							$aI['EM'] = 'Decompression of data failed.';
						} else {
							if (strlen($vZ) != $aP['UCS']) {
								$aI['E']  = 3;
								$aI['EM'] = 'Uncompressed size is not equal with the value in header information.';
							} else {
								if (crc32($vZ) != $aP['CRC']) {
									$aI['E']  = 4;
									$aI['EM'] = 'CRC32 checksum is not equal with the value in header information.';
								}
							}
						}
					}
				}
			}

			$aI['D'] = $vZ;

			// DOS to UNIX timestamp
			$aI['T'] = mktime(($aP['FT']  & 0xf800) >> 11,
							  ($aP['FT']  & 0x07e0) >>  5,
							  ($aP['FT']  & 0x001f) <<  1,
							  ($aP['FD']  & 0x01e0) >>  5,
							  ($aP['FD']  & 0x001f),
							  (($aP['FD'] & 0xfe00) >>  9) + 1980);

			//$this->Entries[] = &new SimpleUnzipEntry($aI);
			$this->package['entries'][] = array(
				'data' => $aI['D'],
				'error' => $aI['E'],
				'error_msg' => $aI['EM'],
				'name' => $aI['N'],
				'path' => $aI['P'],
				'time' => $aI['T']
			);

		} // end for each entries
	}
	function getPackage() {
		return $this->package;
	}
	function getEntryData( $name ) {
		$dir = dirname( $name );
		$name = basename( $name );
		foreach( $this->package['entries'] as $entry)
			if ( $entry['path'] == $dir && $entry['name'] == $name)
				return $entry['data'];
		$this->error('Unknown format');
		return false;
	}
	function getEntryXML( $name ) {
		if ( ($entry_xml = $this->getEntryData( $name ))
			&& ($entry_xmlobj = simplexml_load_string( $entry_xml ))
		)
				return $entry_xmlobj;
		$this->error('Entry not found: '.$name );
		return false;
	}
	function unixstamp( $excelDateTime ) {
		$d = floor( $excelDateTime ); // seconds since 1900
		$t = $excelDateTime - $d;
		return ($d > 0) ? ( $d - 25569 ) * 86400 + $t * 86400 : $t * 86400;
	}
	function error( $set = false ) {
		return ($set) ? $this->error = $set : $this->error;
	}
	function success() {
		return !$this->error;
	}
	function _parse() {
		// Document data holders
		$this->sharedstrings = array();
		$this->sheets = array();

		// Read relations and search for officeDocument
		if ( $relations = $this->getEntryXML("_rels/.rels" ) ) {
			
			foreach ($relations->Relationship as $rel) {
				
				if ($rel["Type"] == SimpleXLSX::SCHEMA_OFFICEDOCUMENT) {
					// Found office document! Read workbook & relations...
					
					// Workbook
					if ( $this->workbook = $this->getEntryXML( $rel['Target'] )) {
						
						if ( $workbookRelations = $this->getEntryXML( dirname($rel['Target']) . '/_rels/workbook.xml.rels' )) {
		
							// Loop relations for workbook and extract sheets...
							foreach ($workbookRelations->Relationship as $workbookRelation) {

								$path = dirname($rel['Target']) . '/' . $workbookRelation['Target'];

								if ($workbookRelation['Type'] == SimpleXLSX::SCHEMA_WORKSHEETRELATION) { // Sheets
								
									if ( $sheet = $this->getEntryXML( $path ) )
										$this->sheets[ str_replace( 'rId', '', (string) $workbookRelation['Id']) ] = $sheet;
													
								} else if ($workbookRelation['Type'] == SimpleXLSX::SCHEMA_SHAREDSTRINGS) {
									
									if ( $sharedStrings = $this->getEntryXML( $path ) ) {
										foreach ($sharedStrings->si as $val) {
											if (isset($val->t)) {
												$this->sharedstrings[] = (string)$val->t;
											} elseif (isset($val->r)) {
												$this->sharedstrings[] = $this->_parseRichText($val);
											}
										}
									}
								}
							}
							
							break;
						}
					}
				}
			}
		}
		// Sort sheets
		ksort($this->sheets);
	}
    private function _parseRichText($is = null) {
        $value = array();

        if (isset($is->t)) {
            $value[] = (string)$is->t;
        } else {
            foreach ($is->r as $run) {
                $value[] = (string)$run->t;
            }
        }

        return implode(' ', $value);
    }
}