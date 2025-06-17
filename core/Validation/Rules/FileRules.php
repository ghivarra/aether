<?php

declare(strict_types = 1);

namespace Aether\Validation\Rules;

use Aether\Validation\Rules\BaseRules;
use Aether\FileUpload;

/**
 * This file is heavily inspired or straight up copy paste from
 * CodeIgniter 4 Validation Library
 *
 * The copyright for this library belong to:
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was in below url:
 * 
 * https://github.com/codeigniter4/CodeIgniter4/blob/develop/LICENSE
 * 
 */

class FileRules extends BaseRules
{
    public function uploaded(array|FileUpload $file): bool
    {
        if (is_array($file))
        {
            foreach ($file as $item):

                $check = $this->uploaded($item);

                if ($check === false)
                {
                    return false;
                }

            endforeach;

            // return
            return true;
        }

        return $file->isValid(); 
    }

    //=====================================================================================================

    public function max_size(array|FileUpload $file, int|string $size = 0): bool
    {
        $size = intval($size);

        if (is_array($file))
        {
            foreach ($file as $item):

                $check = $this->max_size($item, $size);

                if ($check === false)
                {
                    return false;
                }

            endforeach;

            // return
            return true;
        }

        return $file->getSizeByUnit('kb') < $size;
    }

    //=====================================================================================================

    public function is_image(array|FileUpload $file): bool
    {
        if (is_array($file))
        {
            foreach ($file as $item):

                $check = $this->is_image($item);

                if ($check === false)
                {
                    return false;
                }

            endforeach;

            // return
            return true;
        }

        return str_contains($file->getMimeType(), 'image/');
    }

    //=====================================================================================================

    public function mime_in(array|FileUpload $file, string $mime = ''): bool
    {
        if (is_array($file))
        {
            foreach ($file as $item):

                $check = $this->mime_in($item, $mime);

                if ($check === false)
                {
                    return false;
                }

            endforeach;

            // return
            return true;
        }

        $mime = explode(',', $mime);

        return in_array($file->getMimeType(), $mime);
    }

    //=====================================================================================================

    public function ext_in(array|FileUpload $file, string $ext = ''): bool
    {
        if (is_array($file))
        {
            foreach ($file as $item):

                $check = $this->ext_in($item, $ext);

                if ($check === false)
                {
                    return false;
                }

            endforeach;

            // return
            return true;
        }

        $ext = explode(',', $ext);

        return in_array($file->getExtension(), $ext);
    }

    //=====================================================================================================

    public function max_dims(array|FileUpload $file, string $size = '0,0'): bool
    {
        if (is_array($file))
        {
            foreach ($file as $item):

                $check = $this->max_dims($item, $size);

                if ($check === false)
                {
                    return false;
                }

            endforeach;

            // return
            return true;
        }

        if (!$this->is_image($file))
        {
            return false;
        }

        $size = explode(',', $size);

        // getimagesize
        list($imageWidth, $imageHeight, $imageType, $imageAttr) = getimagesize($file->getPath());

        // check
        return ($imageWidth < $size[0]) && ($imageHeight < $size[1]);
    }

    //=====================================================================================================

    public function min_dims(array|FileUpload $file, string $size = '0,0'): bool
    {
        if (is_array($file))
        {
            foreach ($file as $item):

                $check = $this->min_dims($item, $size);

                if ($check === false)
                {
                    return false;
                }

            endforeach;

            // return
            return true;
        }

        if (!$this->is_image($file))
        {
            return false;
        }

        $size = explode(',', $size);

        // getimagesize
        list($imageWidth, $imageHeight, $imageType, $imageAttr) = getimagesize($file->getPath());

        // check
        return ($imageWidth > $size[0]) && ($imageHeight > $size[1]);
    }

    //=====================================================================================================
}