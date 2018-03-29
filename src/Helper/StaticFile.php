<?php
namespace Bybzmt\Framework\Helper;

/**
 * 响应静态文件
 */
class StaticFile
{
    private $_ctx;

    public function __construct($context)
    {
        $this->_ctx = $context;
    }

    public function readfile($file)
    {
        $size = filesize($file);
        $time = filemtime($file);
        $etag = "\"$time-$size\"";

        $header = $this->_ctx->request->header;
        $_etag = isset($header['if_none_match']) ? $header['if_none_match'] : null;

        if ($_etag && $_etag == $etag) {
            $this->_ctx->response->status(304);
            $this->_ctx->response->header("Etag", $etag);
            $this->_ctx->response->header('Last-Modified', gmdate(DATE_RFC850, $time));
            $this->_ctx->response->end();
        } else {
            $this->_ctx->response->header('Content-Type', self::_mime_type($file));
            $this->_ctx->response->header('Content-Length', $size);
            $this->_ctx->response->header("Etag", $etag);
            $this->_ctx->response->header('Last-Modified', gmdate(DATE_RFC850, $time));
            $this->_ctx->response->sendfile($file);
        }
    }

    private static function _mime_type($filename)
    {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/x-icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = substr(strrchr($filename, '.'), 1);

        if (isset($mime_types[$ext])) {
            return $mime_types[$ext];
        } else {
            return 'application/octet-stream';
        }
    }

}
