<?php
/*
	Question2Answer by Gideon Greenspan and contributors
	http://www.question2answer.org/

	File: qa-plugin/markdown-editor/qa-wysiwyg-upload.php
	Description: Page module class for WYSIWYG editor (CKEditor) file upload receiver

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/

class qa_markdown_upload
{
	public function match_request($request)
	{
		return ($request == 'markdown-editor-upload');
	}

	public function process_request($request)
	{
		$message = '';
		$url = '';

		if (is_array($_FILES) && count($_FILES)) {
			require_once QA_INCLUDE_DIR.'app/options.php';
			require_once QA_INCLUDE_DIR.'app/upload.php';

			$upload = qa_upload_file_one(qa_opt('markdowneditor_uploadmax'),true,600);
			$message = @$upload['error'];
			$url = @$upload['bloburl'];
		}

		echo "<blob>$url</blob><error>$message</error>";

		return null;
	}
}
