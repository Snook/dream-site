<?php
require_once 'DAO/File.php';

class CFile extends DAO_File {

	static function uploadFile($file_form_name)
	{
		if (!empty($_FILES[$file_form_name]))
		{
			$temp_filename = $_FILES[$file_form_name]['tmp_name'];

			$File = DAO_CFactory::create('file');

			$file_asset_name = CAppUtil::generateUniqueString();

			if (move_uploaded_file($temp_filename,  ASSETS_PATH . '/file/' . $file_asset_name))
			{
				$File->file_name = str_replace(' ', '_', $_FILES[$file_form_name]['name']);
				$File->file_asset_name = $file_asset_name;
				$File->file_type = $_FILES[$file_form_name]['type'];
				$File->file_size = $_FILES[$file_form_name]['size'];

				$File->insert();
			}

			return $File;
		}

		return false;
	}

	static function getFileDetails($file_id)
	{
		$File = DAO_CFactory::create('file');
		$File->id = $file_id;
		$File->find(true);

		$File->path = ASSETS_PATH . '/file/' . $File->file_asset_name;

		return $File;
	}

	static function deleteFile($file_id)
	{
		$File = self::getFileDetails($file_id);

		unlink($File->path);

		$File->delete();
	}

	static function downloadFile($file_id)
	{
		$File = self::getFileDetails($file_id);

		if ($File)
		{
			if (!empty($File->file_asset_name))
			{
				header('Content-Description: File Transfer');
				header('Content-Type: ' . $File->file_type);
				header('Content-Disposition: attachment; filename=' . $File->file_name);
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . $File->file_size);
				ob_clean();
				flush();
				readfile($File->path);
				exit;
			}
		}

		return false;
	}
}
?>