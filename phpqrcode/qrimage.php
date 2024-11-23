<?php
/*
 * PHP QR Code encoder
 *
 * Image output of code using GD2
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
 
define('QR_IMAGE', true);

class QRimage {

    //----------------------------------------------------------------------    
    public static function png($frame, $filename = false, $pixelPerPoint = 4, $outerFrame = 4, $saveandprint = FALSE) {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if ($filename !== false) {
            // Check if the directory exists, if not, create it
            $dir = dirname($filename);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);  // Create the directory with appropriate permissions
            }

            // Check if the file already exists
            if (file_exists($filename)) {
                echo "Notice: The QR code for this data has already been generated and saved.";
            } else {
                // Save and optionally print the image
                if ($saveandprint === TRUE) {
                    imagepng($image, $filename);
                    header("Content-type: image/png");
                    imagepng($image);
                } else {
                    imagepng($image, $filename);
                }
            }
        } else {
            // Output to browser if no filename is given
            header("Content-type: image/png");
            imagepng($image);
        }

        imageDestroy($image);
    }

    //----------------------------------------------------------------------    
    public static function jpg($frame, $filename = false, $pixelPerPoint = 8, $outerFrame = 4, $q = 85) {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);

        if ($filename === false) {
            Header("Content-type: image/jpeg");
            ImageJpeg($image, null, $q);
        } else {
            ImageJpeg($image, $filename, $q);            
        }

        ImageDestroy($image);
    }

    //----------------------------------------------------------------------    
    private static function image($frame, $pixelPerPoint = 4, $outerFrame = 4) {
        $h = count($frame);
        $w = strlen($frame[0]);

        $imgW = $w + 2 * $outerFrame;
        $imgH = $h + 2 * $outerFrame;

        $base_image = ImageCreate($imgW, $imgH);

        $col[0] = ImageColorAllocate($base_image, 255, 255, 255); // white
        $col[1] = ImageColorAllocate($base_image, 0, 0, 0);     // black

        imagefill($base_image, 0, 0, $col[0]);

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ($frame[$y][$x] == '1') {
                    ImageSetPixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]); 
                }
            }
        }

        $target_image = ImageCreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
        ImageCopyResized($target_image, $base_image, 0, 0, 0, 0, $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH);
        ImageDestroy($base_image);

        return $target_image;
    }
}
?>
