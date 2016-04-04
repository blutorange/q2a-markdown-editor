<?php
/*
	Question2Answer Markdown editor plugin
	License: http://www.gnu.org/licenses/gpl.html
*/

class qa_markdown_editor
{

	private $pluginurl;
	private $cssopt = 'markdown_editor_css';
	private $convopt = 'markdown_comment';
	private $hljsopt = 'markdown_highlightjs';

	public function load_module($directory, $urltoroot)
	{
		require_once QA_INCLUDE_DIR.'app/upload.php';
		$this->pluginurl = $urltoroot;
	}

	public function option_default($option)
	{
		if ($option == 'markdowneditor_uploadmax') {
			require_once QA_INCLUDE_DIR.'app/upload.php';
			return min(qa_get_max_upload_size(), 1048576);
		}
	}

	public function calc_quality($content, $format)
	{
		return $format == 'markdown' ? 1.0 : 0.8;
	}

	public function get_field(&$qa_content, $content, $format, $fieldname, $rows, $autofocus)
	{
		$html = '<div id="wmd-button-bar-'.$fieldname.'" class="wmd-button-bar"></div>' . "\n";
		$html .= '<textarea name="'.$fieldname.'" id="wmd-input-'.$fieldname.'" class="wmd-input">'.$content.'</textarea>' . "\n";
		$html .= '<h3>'.qa_lang_html('markdown/preview').'</h3>' . "\n";
		$html .= '<div id="wmd-preview-'.$fieldname.'" class="wmd-preview"></div>' . "\n";

         $html .= '<link rel="stylesheet" type="text/css" href="'.$this->pluginurl.'pagedown/github.css"></link>' . "\n";
         $html .= '<script src="'.$this->pluginurl.'pagedown/highlight.pack.js"></script>' . "\n";

         $html .= '<script src="'.$this->pluginurl.'pagedown/Markdown.All.min.js"></script>' . "\n";

		 // Uncomment these and comment the above to use the unminified code.
         //$html .= '<script src="'.$this->pluginurl.'pagedown/Markdown.Converter.js"></script>' . "\n";
         //$html .= '<script src="'.$this->pluginurl.'pagedown/Markdown.Sanitizer.js"></script>' . "\n";
         //$html .= '<script src="'.$this->pluginurl.'pagedown/Markdown.Editor.js"></script>' . "\n";
         //$html .= '<script src="'.$this->pluginurl.'pagedown/Markdown.Extra.js"></script>' . "\n";

		return array('type'=>'custom', 'html'=>$html);
	}

	public function read_post($fieldname)
	{
		$html = $this->_my_qa_post_text($fieldname);

		return array(
			'format' => 'markdown',
			'content' => $html
		);
	}

	public function load_script($fieldname)
	{
		$usehljs = qa_opt($this->hljsopt) === '1';
		$script = 
            'var converter = Markdown.getSanitizingConverter();' . "\n" .
			'Markdown.Extra.init(converter,{extensions:"all",highlighter:"highlight"});' . "\n" .
			'var editor = new Markdown.Editor(converter, "-'.$fieldname.'");' . "\n";
		if ($usehljs) {
			$script .= 'editor.hooks.chain("onPreviewRefresh",function(){Array.prototype.forEach.call(document.querySelectorAll("pre code"),hljs.highlightBlock);});' . "\n";
		}
		$script .= 'editor.run();' . "\n";
		return $script;
	}


	// set admin options
	public function admin_form(&$qa_content)
	{
		$saved_msg = null;

		require_once QA_INCLUDE_DIR.'app/upload.php';

		if (qa_clicked('markdown_save')) {
			// save options
			$hidecss = qa_post_text('md_hidecss') ? '1' : '0';
			qa_opt($this->cssopt, $hidecss);
			$convert = qa_post_text('md_comments') ? '1' : '0';
			qa_opt($this->convopt, $convert);
			$convert = qa_post_text('md_highlightjs') ? '1' : '0';
			qa_opt($this->hljsopt, $convert);
			qa_opt("markdowneditor_uploadmax", min(qa_get_max_upload_size(), 1048576*(float)qa_post_text('md_uploadmax')));
			
			$saved_msg = qa_lang_html('admin/options_saved');
		}


		return array(
			'ok' => $saved_msg,
					'style' => 'wide',

			'fields' => array(
				'css' => array(
					'type' => 'checkbox',
					'label' => qa_lang_html('markdown/admin_hidecss'),
					'tags' => 'NAME="md_hidecss"',
					'value' => qa_opt($this->cssopt) === '1',
					'note' => qa_lang_html('markdown/admin_hidecss_note'),
				),
				'comments' => array(
					'type' => 'checkbox',
					'label' => qa_lang_html('markdown/admin_comments'),
					'tags' => 'NAME="md_comments"',
					'value' => qa_opt($this->convopt) === '1',
					'note' => qa_lang_html('markdown/admin_comments_note'),
				),
				'highlightjs' => array(
					'type' => 'checkbox',
					'label' => qa_lang_html('markdown/admin_syntax'),
					'tags' => 'NAME="md_highlightjs"',
					'value' => qa_opt($this->hljsopt) === '1',
					'note' => qa_lang_html('markdown/admin_syntax_note'),
				),
				'uploadmax' => array(
					'label' => qa_lang_html('markdown/admin_uploadmax'),
					'suffix' => 'MB (max '.$this->bytes_to_mega_html(qa_get_max_upload_size()).')',
					'type' => 'number',
					'value' => $this->bytes_to_mega_html(qa_opt("markdowneditor_uploadmax")),
					'tags' => 'NAME="md_uploadmax"',
				),
			),

			'buttons' => array(
				'save' => array(
					'tags' => 'NAME="markdown_save"',
					'label' => qa_lang_html('admin/save_options_button'),
					'value' => '1',
				),
			),
		);
	}

	private function bytes_to_mega_html($bytes)
	{
		return qa_html(number_format($bytes/1048576, 1));
	}

	// copy of qa-base.php > qa_post_text, with trim() function removed.
	private function _my_qa_post_text($field)
	{
		return isset($_POST[$field]) ? preg_replace('/\r\n?/', "\n", qa_gpc_to_string($_POST[$field])) : null;
	}
}
