<?php

/**
 * Subvitamine\Pdf/Paginator is a class that allows you to add text to any PDF you want.
 */

namespace Subvitamine\Pdf;

class Paginator
{
    public function __construct()
    {
    }

    /**
     * Draw is the main function of this class, it writes the text you specify on the PDF file.
     * @param  [string] $pdf_base64    Base64 version of the PDF
     * @param  [array] $lines  Array of lines to add to the PDF
     */
    public function draw($pdf_text, $lines)
    {

        // We create a PDF file from text
        $pdf = \Zend_Pdf::parse($pdf_text, 1);

        // Style creation
        $style = new \Zend_Pdf_Style();
        $style->setFillColor(new \Zend_Pdf_Color_HTML('#adb5bd'));

        // New font
        $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA);

        $pageCount = count($pdf->pages);

        //For each page of our PDF file
        foreach ($pdf->pages as $key => $page) {

            // How far from the bottom our text will be
            $firstLineY = 20;

            // Apply the font
            $page->setFont($font, 7);
            $page->setStyle($style);

            // Draw our pagination text
            $page->drawText('Page '.($key + 1).'/'.$pageCount, 540, $firstLineY, 'UTF-8');

            // Draw our footer lines
            foreach ($lines as $line) {
                if (! empty($line['color'])) {
                    $page->setFillColor(\Zend_Pdf_Color_Html::color($line['color']))
                        ->drawText($line['text'], $line['posX'], $firstLineY, 'UTF-8');
                } else {
                    $page->drawText($line['text'], $line['posX'], $firstLineY, 'UTF-8');
                }

                $firstLineY = $firstLineY + 10;
            }
        }

        // Return our rendered PDF file
        return $pdf->render();
    }
}
