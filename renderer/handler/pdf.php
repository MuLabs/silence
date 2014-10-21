<?php
namespace Mu\Kernel\Renderer\Handler;

use Mu\Kernel;

class Pdf extends Kernel\Renderer\Handler
{
	/**
	 * @inheritdoc
	 */
	public function render(Kernel\View\View $view)
	{
        // Get variables:
        $display  = $view->getVar('pdfDisplay', 'fullpage');
        $filename = $view->getVar('pdfFilename', 'file');
        $language = $view->getVar('pdfLanguage', 'fr');
        $author   = $view->getVar('pdfAuthor', '');
        $title    = $view->getVar('pdfTitle', $filename);
        $subject  = $view->getVar('pdfSubject', $filename);

        // Complete Pdf file:
        $pdf = new \HTML2PDF('P', 'A4', $language);
        $pdf->pdf->SetDisplayMode($display);
        $pdf->pdf->SetAuthor($author);
        $pdf->pdf->SetTitle($title);
        $pdf->pdf->SetSubject($subject);

        // Set fonts if needed:
        $aFonts = $view->getVar('pdfFonts', array());
        foreach ($aFonts as $key => $values) {
            if (!isset($values['name'])) {
                continue;
            }

            if ($key == 'default') {
                $pdf->setDefaultFont($values['name']);
            } else {
                $style = (isset($values['style'])) ? $values['style'] : '';
                $size  = (isset($values['size']))  ? $values['size']  : 12;
                $file  = (isset($values['file']))  ? $values['file']  : '';
                $pdf->pdf->SetFont($values['name'], $style, $size, $file);
            }
        }

        // Set text shadows if needed:
        $aShadows = $view->getVar('pdfShadows', array());
        if (!empty($aShadows)) {
            $pdf->pdf->setTextShadow($aShadows);
        }

        // Render the pdf constant:
        $pdf->writeHTML($view->render());
        ob_clean();

        // Launch download:
        $pdf->Output($filename . '.pdf', 'D');

        // Throw Exception:
        throw new Kernel\EndException();
	}

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return 'application/pdf';
    }
}