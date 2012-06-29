<?php

if(extensions::isSelected('viewrenderer_razor')) {
	class viewrenderer_razor {
		public static $renderer = null;
		public static $extension;
		public static $templatePath;
		public static $compiledPath;

		public static function extension_info() {
			return array(
				'name' => 'viewrenderer: razor',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function extension_load() {
			events::register('renderview', events::Callback('viewrenderer_razor::renderview'));

			self::$extension = config::get('/razor/templates/@extension', '.cshtml');
			self::$templatePath = framework::translatePath(config::get('/razor/templates/@templatePath', '{app}views'));
			self::$compiledPath = framework::translatePath(config::get('/razor/templates/@compiledPath', '{app}writable/compiledViews'));
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			$tInputFile = self::$templatePath . '/' . $uObject['viewFile'];
			$tOutputFile = self::$compiledPath . '/rzr_' . $uObject['viewFile']; // . QEXT_PHP

			// cengiz: Render if file not exist
			// or debug mode on
			if(framework::$development || !file_exists($tOutputFile)) {
				if(is_null(self::$renderer)) {
					self::$renderer = new RazorViewRenderer();
				}

				self::$renderer->generateViewFile($tInputFile, $tOutputFile);
			}

			// variable extraction
			$model = &$uObject['model'];
			if(is_array($model)) {
				extract($model, EXTR_SKIP|EXTR_REFS);
			}

			extract($uObject['extra'], EXTR_SKIP|EXTR_REFS);

			require($tOutputFile);
		}
	}

	/**
	 * RazorViewRenderer implements a view renderer that allows to use "Razor" template syntax.
	 *
	 * To use RazorViewRenderer, configure it as an application component named "viewRenderer" in the application configuration:
	 * <pre>
	 * array(
	 *     'components'=>array(
	 *         ......
	 *         'viewRenderer'=>array(
	 *             'class'=>'path.to.class.RazorViewRenderer',
	 *             'fileExtension'=>'.tpl',
	 *         ),
	 *     ),
	 * )
	 * </pre>
	 *
	 * Razor does not require you to explicitly close the code-block.
	 * Content emitted using a @: block is automatically HTML encoded to better protect against
	 * XSS attack scenarios.
	 *
	 * Razor minimizes the number of characters and keystrokes required in a file, and enables a fast,
	 * fluid coding workflow. Unlike most template syntaxes, you do not need to interrupt your coding
	 * to explicitly denote server blocks within your HTML.
	 * The parser is smart enough to infer this from your code. This enables a really compact and expressive
	 * syntax which is clean, fast and fun to type.
	 *
	 * Razor is easy to learn and enables you to quickly be productive with a minimum of concepts.
	 * You use all your existing language and HTML skills.
	 *
	 * RazorViewRenderer allows you to write view files with the following syntax:
	 * <pre>
	 * Simple statement output, will be replaced with &lt;?php echo ... ?&gt;:
	 * &lt;div class="content"&gt;@date("Y-m-d H:i:s")&lt;/div&gt;
	 * &lt;span&gt;@$someVariable&lt;/span&gt;
	 * &lt;a href="@Yii::app()-&gt;params['baseUrl']/default.php"&gt;This is link&lt;/a&gt;
	 * &lt;pre&gt;@$obj-&gt;getContent()&lt;/pre&gt;
	 *
	 * The @( ) syntax enables a code block to have multiple tokens.
	 * For example, you could re-write the above code to concatenate a string and the number together
	 * within a @( code ) block:
	 * &lt;p&gt;ID: @("Your Id is: " . $id)&lt;/p&gt;
	 *
	 * Or you can use ?: statement:
	 * &lt;div&gt;Status: @($activeFlag ? 'Active' : 'Not active')&lt;/div&gt;
	 *
	 * You can denote multiple lines of code by wrapping it within a @{ code } block:
	 * @{
	 *      $obj = new Obj('a', 'b');
	 *      $another_var = $obj-&gt;getProperty() + 1;
	 * }
	 *
	 * Loops examples(valid statements @foreach, @for and @while):
	 * @foreach ($menu as $item) {
	 *     This is some block of HTML.&lt;br/&gt;
	 *     &lt;a href="@$item['url']"&gt;@$item['title']&lt;/a&gt;&lt;br/&gt;
	 * }
	 * 
	 * If-else examples:
	 * @if($variable == "some value") {
	 *     Could be some &lt;p&gt;HTML&lt;/p&gt; here.
	 *     Current date: @date("Y-m-d")
	 * }
	 *
	 * or with else/elseif blocks:
	 * @if ($someExpression) {
	 *     some html or @Html::helper(array('a' =&gt; 'b'), true)
	 * } elseif ($anotherExpression) {
	 *     &lt;div&gt;some html&lt;/div&gt; and someField value is: @$obj-&gt;method1($a, $b)-&gt;method2()-&gt;someField
	 * } else {
	 *     &lt;div&gt;some html&lt;/div&gt; and ENUM_CONST value is: @ENUM_CONST
	 * }
	 *
	 * Each @foreach, @for, @while, @if could be written in one line.
	 * Block brackets {,} - are required:
	 * &lt;div&gt;@for($i=1; $i &lt;= 10; ++$i) { @$i&nbsp; }&lt;/div&gt;
	 *
	 * You could use nested statements in these blocks:
	 * @while(!$files-&gt;isEmpty()) {
	 *      @if ($files-&gt;needHeader) {
	 *          &lt;h1&gt;Some html&lt;/h1&gt;
	 *      }
	 *      ...
	 *      ... other html
	 * }
	 * </pre>
	 *
	 * @author Stepan Kravchenko <stepan.krab@gmail.com>
	 * @version 1.0.1
	 */
	class RazorViewRenderer
	{
		private $_input;
		private $_output;
		private $_sourceFile;

		/**
		 * Parses the source view file and saves the results as another file.
		 * This method is required by the parent class.
		 * @param string the source view file path
		 * @param string the resulting view file path
		 */
		public function generateViewFile($sourceFile, $viewFile)
		{
			$this->_sourceFile = $sourceFile;
			$this->_input = file_get_contents($sourceFile);
			$this->_output = "<?php /* source file: {$sourceFile} */ ?>\n";

			$this->parse(0, strlen($this->_input));
			
			file_put_contents($viewFile, $this->_output);
		}

		/**
		 * Parse block of input template file from $beginBlock position to ($endBlock - 1).
		 * Replaced all valid @-statement of Razor template syntax.
		 *
		 * @param int $beginBlock First symbol of block to parse.
		 * @param int $endBlock The position after last symbol of block to parse.
		 */
		private function parse($beginBlock, $endBlock)
		{
			$offset = $beginBlock;
			while (($p = strpos($this->_input, "@", $offset)) !== false && $p < $endBlock) {
				// replace @@ -> @
				if ($this->isNextToken($p, $endBlock, "@")) {
					$this->_output .= substr($this->_input, $offset, $p - $offset + 1);
					$offset = $p + 2;
					continue;
				}

				// replace multi-token statements @(...)
				if ($this->isNextToken($p, $endBlock, "(")) {
					$end = $this->findClosingBracket($p + 1, $endBlock, "(", ")");
					$this->_output .= substr($this->_input, $offset, $p - $offset);
					$this->generatePHPOutput($p, $end);
					$offset = $end + 1;
					continue;
				}

				// replace multi-line statements @{...}
				if ($this->isNextToken($p, $endBlock, "{")) {
					$end = $this->findClosingBracket($p + 1, $endBlock, "{", "}");
					$this->_output .= substr($this->_input, $offset, $p - $offset);
					$this->_output .= "<?php " . substr($this->_input, $p + 2, $end - $p - 2) . " ?>";
					$offset = $end + 1;
					continue;
				}

				// replace HTML-encoded statements @:...
				if ($this->isNextToken($p, $endBlock, ":")) {
					$statement = $this->detectStatement($p + 2, $endBlock);
					$end = $this->findEndStatement($p + 1 + strlen($statement), $endBlock);
					$this->_output .= substr($this->_input, $offset, $p - $offset);
					$this->generatePHPOutput($p + 1, $end, true);
					$offset = $end + 1;
					continue;
				}

				$statement = $this->detectStatement($p + 1, $endBlock);
				if ($statement == "foreach" || $statement == "for" || $statement == "while") {
					$offset = $this->processLoopStatement($p, $offset, $endBlock, $statement);
				} elseif ($statement == "if") {
					$offset = $this->processIfStatement($p, $offset, $endBlock, $statement);
				} else {
					$end = $this->findEndStatement($p + strlen($statement), $endBlock);
					$this->_output .= substr($this->_input, $offset, $p - $offset);
					$this->generatePHPOutput($p, $end);
					$offset = $end + 1;
				}
			}

			$this->_output .= substr($this->_input, $offset, $endBlock - $offset);
		}

		private function generatePHPOutput($currentPosition, $endPosition, $htmlEncode = false)
		{
			$this->_output .= "<?php echo "
					. ($htmlEncode ? "CHtml::encode(" : "")
					. substr($this->_input, $currentPosition + 1, $endPosition - $currentPosition)
					. ($htmlEncode ? ")" : "")
					. "; ?>";
		}

		private function processLoopStatement($currentPosition, $offset, $endBlock, $statement)
		{
			if (($bracketPosition = $this->findOpenBracketAtLine($currentPosition + 1, $endBlock)) === false) {
				throw new RazorViewRendererException("Cannot find open bracket for '{$statement}' statement.",
						$this->_sourceFile, $this->getLineNumber($currentPosition));
			}

			$this->_output .= substr($this->_input, $offset, $currentPosition - $offset);
			$this->_output .= "<?php " . substr($this->_input, $currentPosition + 1, $bracketPosition - $currentPosition) . " ?>";
			$offset = $bracketPosition + 1;

			$end = $this->findClosingBracket($bracketPosition, $endBlock, "{", "}");
			$this->parse($offset, $end);
			$this->_output .= "<?php } ?>";

			return $end + 1;
		}

		private function processIfStatement($currentPosition, $offset, $endBlock, $statement)
		{
			$bracketPosition = $this->findOpenBracketAtLine($currentPosition + 1, $endBlock);
			if ($bracketPosition === false) {
				throw new RazorViewRendererException("Cannot find open bracket for '{$statement}' statement.",
					$this->_sourceFile, $this->getLineNumber($currentPosition));
			}

			$this->_output .= substr($this->_input, $offset, $currentPosition - $offset);
			$start = $currentPosition + 1;
			while (true) {
				$this->_output .= "<?php " . substr($this->_input, $start, $bracketPosition - $start + 1) . " ?>";
				$offset = $bracketPosition + 1;

				$end = $this->findClosingBracket($bracketPosition, $endBlock,  "{", "}");
				$this->parse($offset, $end);
				$offset = $end + 1;

				$bracketPosition = $this->findOpenBracketAtLine($offset, $endBlock);
				if ($bracketPosition === false) {
					$this->_output .= "<?php } ?>";
					break;
				}

				$start = $end;
			}

			return $offset;
		}

		private function findOpenBracketAtLine($currentPosition, $endBlock)
		{
			$openDoubleQuotes = false;
			$openSingleQuotes = false;

			for ($p = $currentPosition; $p < $endBlock; ++$p) {
				if ($this->_input[$p] == "\n") {
					return false;
				}

				$quotesNotOpened = !$openDoubleQuotes && !$openSingleQuotes;
				if ($this->_input[$p] == '"') {
					$openDoubleQuotes = $this->getQuotesState($openDoubleQuotes, $quotesNotOpened, $p);
				} elseif ($this->_input[$p] == "'") {
					$openSingleQuotes = $this->getQuotesState($openSingleQuotes, $quotesNotOpened, $p);
				} elseif ($this->_input[$p] == "{" && $quotesNotOpened) {
					return $p;
				}
			}

			return false;
		}

		private function isNextToken($currentPosition, $endBlock, $token)
		{
			return $currentPosition + strlen($token) < $endBlock
					&& substr($this->_input, $currentPosition + 1, strlen($token)) == $token;
		}

		private function isEscaped($currentPosition)
		{
			$cntBackSlashes = 0;
			for ($p = $currentPosition - 1; $p >= 0; --$p) {
				if ($this->_input[$p] != "\\") {
					break;
				}

				++$cntBackSlashes;
			}

			return $cntBackSlashes % 2 == 1;
		}

		private function getQuotesState($testedQuotes, $quotesNotOpened, $currentPosition)
		{
			if ($quotesNotOpened) {
				return true;
			}

			return $testedQuotes && !$this->isEscaped($currentPosition) ? false: $testedQuotes;
		}

		private function findClosingBracket($openBracketPosition, $endBlock, $openBracket, $closeBracket)
		{
			$opened = 0;
			$openDoubleQuotes = false;
			$openSingleQuotes = false;

			for ($p = $openBracketPosition; $p < $endBlock; ++$p) {
				$quotesNotOpened = !$openDoubleQuotes && !$openSingleQuotes;

				if ($this->_input[$p] == '"') {
					$openDoubleQuotes = $this->getQuotesState($openDoubleQuotes, $quotesNotOpened, $p);
				} elseif ($this->_input[$p] == "'") {
					$openSingleQuotes = $this->getQuotesState($openSingleQuotes, $quotesNotOpened, $p);
				} elseif ($this->_input[$p] == $openBracket && $quotesNotOpened) {
					$opened++;
				} elseif ($this->_input[$p] == $closeBracket && $quotesNotOpened) {
					if (--$opened == 0) {
						return $p;
					}
				}
			}

			throw new RazorViewRendererException("Cannot find closing bracket.", $this->_sourceFile,
					$this->getLineNumber($openBracketPosition));
		}

		private function findEndStatement($endPosition, $endBlock)
		{
			if ($this->isNextToken($endPosition, $endBlock, "(")) {
				$endPosition = $this->findClosingBracket($endPosition + 1, $endBlock, "(", ")");
				$endPosition = $this->findEndStatement($endPosition, $endBlock);
			} elseif ($this->isNextToken($endPosition, $endBlock, "[")) {
				$endPosition = $this->findClosingBracket($endPosition + 1, $endBlock, "[", "]");
				$endPosition = $this->findEndStatement($endPosition, $endBlock);
			} elseif ($this->isNextToken($endPosition, $endBlock, "->")) {
				$endPosition += 2;
				$statement = $this->detectStatement($endPosition + 1, $endBlock);
				$endPosition = $this->findEndStatement($endPosition + strlen($statement), $endBlock);
			} elseif ($this->isNextToken($endPosition, $endBlock, "::")) {
				$endPosition += 2;
				$statement = $this->detectStatement($endPosition + 1, $endBlock);
				$endPosition = $this->findEndStatement($endPosition + strlen($statement), $endBlock);
			}

			return $endPosition;
		}

		private function detectStatement($currentPosition, $endBlock)
		{
			$invalidCharPosition = $endBlock;
			for ($p = $currentPosition; $p < $invalidCharPosition; ++$p) {
				if ($this->_input[$p] == "$" && $p == $currentPosition) {
					continue;
				}

				if (preg_match('/[a-zA-Z0-9_]/', $this->_input[$p])) {
					continue;
				}

				$invalidCharPosition = $p;
				break;
			}

			if ($currentPosition == $invalidCharPosition) {
				throw new RazorViewRendererException("Cannot detect statement.", $this->_sourceFile,
					$this->getLineNumber($currentPosition));
			}

			return substr($this->_input, $currentPosition, $invalidCharPosition - $currentPosition);
		}

		private function getLineNumber($currentPosition)
		{
			return count(explode("\n", substr($this->_input, 0, $currentPosition)));
		}
	}

	/**
	 * RazorViewRendererException represents a generic exception for razor view render extension.
	 *
	 * @author Stepan Kravchenko <stepan.krab@gmail.com>
	 * @version 1.0.0
	 */
	class RazorViewRendererException
	{
		public function __construct($message, $templateFileName, $line)
		{
			parent::__construct("Invalid view template: {$templateFileName}, at line {$line}. {$message}", null, null);
		}
	}
}

?>