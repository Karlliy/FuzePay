/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.filebrowserBrowseUrl =  '../ckeditor/ckfinder/ckfinder.html';  
    config.filebrowserImageBrowseUrl =  '../ckeditor/ckfinder/ckfinder.html?type=Images';  
    config.filebrowserFlashBrowseUrl =  '../ckeditor/ckfinder/ckfinder.html?type=Flash';  
    config.filebrowserUploadUrl =  '../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';  
    config.filebrowserImageUploadUrl =  '../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';  
    config.filebrowserFlashUploadUrl =  '../ckeditor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';  
    config.filebrowserWindowWidth = '1000';  
    config.filebrowserWindowHeight = '700';  
    config.language =  "zh-tw" ; 
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
};
CKFinder.SetupCKEditor(null, '../ckeditor/ckfinder/');//注意ckfinder的路径对应实际放置的位置