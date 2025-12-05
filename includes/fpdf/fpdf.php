<?php
// Simplified FPDF Library - Version 1.81
// For PDF generation in PHP

class FPDF
{
    protected $currentpage;
    protected $n;
    protected $offsets;
    protected $buffer;
    protected $pages;
    protected $state;
    protected $compress;
    protected $K;
    protected $wPt;
    protected $hPt;
    protected $w;
    protected $h;
    protected $lMargin;
    protected $tMargin;
    protected $rMargin;
    protected $bMargin;
    protected $cMargin;
    protected $x;
    protected $y;
    protected $lasth;
    protected $LineWidth;
    protected $fontPath;
    protected $fonts;
    protected $FontFamily;
    protected $FontStyle;
    protected $underline;
    protected $CurrentFont;
    protected $FontSizePt;
    protected $FontSize;
    protected $DrawColor;
    protected $FillColor;
    protected $TextColor;
    protected $ColorFlag;
    protected $ws;

    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->fontPath = dirname(__FILE__) . '/font/';
        $this->fonts = [];
        
        // Dimensions
        $this->w = 210;
        $this->h = 297;
        if($orientation=='L')
        {
            $temp = $this->w;
            $this->w = $this->h;
            $this->h = $temp;
        }
        $this->wPt = $this->w * 2.834645669;
        $this->hPt = $this->h * 2.834645669;
        $this->x = 10;
        $this->y = 10;
        $this->lMargin = 10;
        $this->tMargin = 10;
        $this->rMargin = 10;
        $this->bMargin = 10;
        $this->cMargin = 0;
        $this->LineWidth = 0.567;
        
        $this->currentpage = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->offsets = [];
        $this->pages = [];
        $this->state = 0;
        $this->compress = true;
        $this->K = 2.834645669;
        
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->FontSize = 12 / $this->K;
        $this->underline = false;
        $this->CurrentFont = [];
        $this->DrawColor = '0 0 0';
        $this->FillColor = '255 255 255';
        $this->TextColor = '0 0 0';
        $this->ColorFlag = false;
        $this->ws = 0;
        
        // Add core fonts
        $this->AddFont('Arial', '', 'helvetica');
        $this->AddFont('Arial', 'B', 'helveticab');
        $this->AddFont('Arial', 'I', 'helveticai');
        $this->AddFont('Arial', 'BI', 'helveticabi');
        $this->AddFont('Courier', '', 'courier');
        $this->AddFont('Courier', 'B', 'courierb');
        $this->AddFont('Courier', 'I', 'courieri');
        $this->AddFont('Courier', 'BI', 'courierbi');
        $this->AddFont('Times', '', 'times');
        $this->AddFont('Times', 'B', 'timesb');
        $this->AddFont('Times', 'I', 'timesi');
        $this->AddFont('Times', 'BI', 'timesbi');
        $this->state = 1;
    }

    function AddFont($family, $style='', $file='')
    {
        $family = strtolower($family);
        if($file=='')
            $file = str_replace(' ', '', $family).strtolower($style);
        $file = strtoupper($file);
        $this->fonts[$family.$style] = array('i'=>($this->n+1), 'type'=>'core', 'name'=>'Helvetica', 'up'=>-100, 'ut'=>50, 'cw'=>'');
    }

    function SetFont($family, $style='', $size=0)
    {
        $family = strtolower($family);
        if($family=='')
            $family = $this->FontFamily;
        if(strpos($family,'courier')===0)
            $family = 'courier';
        elseif(strpos($family,'arial')===0 || strpos($family,'helvetica')===0)
            $family = 'arial';
        elseif(strpos($family,'times')===0)
            $family = 'times';
        else
            $family = 'arial';
        
        if($style=='')
            $style = $this->FontStyle;
        else
        {
            $style = strtoupper($style);
            if(strpos($style,'U')!==false)
            {
                $this->underline = true;
                $style = str_replace('U', '', $style);
            }
            else
                $this->underline = false;
        }
        if($size==0)
            $size = $this->FontSizePt;
        
        if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
            return;
        
        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->K;
        $this->CurrentFont = $this->fonts[$family.$style];
    }

    function SetFontSize($size)
    {
        if($this->FontSizePt==$size)
            return;
        $this->FontSizePt = $size;
        $this->FontSize = $size / $this->K;
    }

    function AddPage($orientation='')
    {
        if($this->state==3)
            $this->EndDoc();
        
        $this->currentpage++;
        $this->pages[$this->currentpage] = '';
        $this->state = 2;
        
        $this->SetFont('Arial', '', 12);
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->Header();
    }

    function Header()
    {
    }

    function Footer()
    {
    }

    function PageNo()
    {
        return $this->currentpage;
    }

    function SetDrawColor($r, $g=NULL, $b=NULL)
    {
        if(is_string($r) && strlen($r)==7)
        {
            $r = hexdec(substr($r,1,2));
            $g = hexdec(substr($r,3,2));
            $b = hexdec(substr($r,5,2));
        }
        if($g==NULL)
            $g = $r;
        if($b==NULL)
            $b = $r;
        $this->DrawColor = sprintf('%.3f %.3f %.3f RG', $r/255, $g/255, $b/255);
    }

    function SetFillColor($r, $g=NULL, $b=NULL)
    {
        if(is_string($r) && strlen($r)==7)
        {
            $r = hexdec(substr($r,1,2));
            $g = hexdec(substr($r,3,2));
            $b = hexdec(substr($r,5,2));
        }
        if($g==NULL)
            $g = $r;
        if($b==NULL)
            $b = $r;
        $this->FillColor = sprintf('%.3f %.3f %.3f rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor!=$this->DrawColor);
    }

    function SetTextColor($r, $g=NULL, $b=NULL)
    {
        if(is_string($r) && strlen($r)==7)
        {
            $r = hexdec(substr($r,1,2));
            $g = hexdec(substr($r,3,2));
            $b = hexdec(substr($r,5,2));
        }
        if($g==NULL)
            $g = $r;
        if($b==NULL)
            $b = $r;
        $this->TextColor = sprintf('%.3f %.3f %.3f rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor!=$this->DrawColor);
    }

    function GetStringWidth($s)
    {
        $s = (string)$s;
        $cw = &$this->CurrentFont['cw'];
        if($cw=='')
            $cw = [80];
        $w = 0;
        $l = strlen($s);
        for($i=0;$i<$l;$i++)
            $w += $cw[ord($s[$i])];
        return $w * $this->FontSize / 1000;
    }

    function SetLineWidth($width)
    {
        $this->LineWidth = $width;
    }

    function Line($x1, $y1, $x2, $y2)
    {
        $this->_out(sprintf('%.2f %.2f m %.2f %.2f l s', $x1*$this->K, ($this->h-$y1)*$this->K, $x2*$this->K, ($this->h-$y2)*$this->K));
    }

    function Rect($x, $y, $w, $h, $style='')
    {
        $k = $this->K;
        $op = $style=='F' ? 'f' : ($style=='FD' || $style=='DF' ? 'B' : 'S');
        $this->_out(sprintf('%.2f %.2f %.2f %.2f re %s', $x*$k, ($this->h-$y)*$k, $w*$k, -$h*$k, $op));
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        $k = $this->K;
        if($this->y+$h>$this->h-$this->bMargin)
        {
            $this->AddPage($this->CurOrientation);
        }
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $s = '';
        if($fill || $border==1)
        {
            if($fill)
                $s .= sprintf('%.2f %.2f %.2f %.2f re f ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k);
            if($border==1)
                $s .= sprintf('%.2f %.2f %.2f %.2f re S ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k);
        }
        if($txt!='')
        {
            if($align=='R')
                $tx = $this->x+$w-$this->cMargin;
            elseif($align=='C')
                $tx = $this->x+$w/2;
            else
                $tx = $this->x+$this->cMargin;
            $s .= sprintf('BT %.2f %.2f Td (%s) Tj ET', $tx*$k, ($this->h-$this->y-$this->FontSize)*$k, $this->_escape($txt));
        }
        if($s)
            $this->_out($s);
        $this->lasth = $h;
        if($ln>0)
        {
            $this->y += $h;
            if($ln==1)
                $this->x = $this->lMargin;
        }
        else
            $this->x += $w;
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSizePt;
        $s = str_replace("\r", '', $txt);
        if($this->CurrentFont['cw']==='')
        {
            $nb = strlen($s);
            while($nb>0 && $s[$nb-1]=="\n")
                $nb--;
        }
        else
        {
            $nb = 0;
        }
        $b = 0;
        if($border)
        {
            if($border==1)
                $border = 'LTRB';
            $b = (strpos($border, 'L')!==false ? 1 : 0) + (strpos($border, 'R')!==false ? 2 : 0);
            $b2 = (strpos($border, 'T')!==false ? 1 : 0) + (strpos($border, 'B')!==false ? 2 : 0);
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                if($this->ws>0)
                {
                    $this->ws = 0;
                    $this->_out('0 Tw');
                }
                $this->Cell($w, $h, substr($s, $j, $i-$j), $b2, 1, $align, $fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
                continue;
            }
            if($c==' ')
            {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $this->CurrentFont['cw'][ord($c)];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    if($this->ws>0)
                    {
                        $this->ws = 0;
                        $this->_out('0 Tw');
                    }
                    $this->Cell($w, $h, substr($s, $j, $i-$j), $b2, 1, $align, $fill);
                }
                else
                {
                    if($align=='J')
                    {
                        $this->ws = ($ns>1) ? ($wmax-$ls)/($ns-1) : 0;
                        $this->_out(sprintf('%.3f Tw', $this->ws*$this->k));
                    }
                    $this->Cell($w, $h, substr($s, $j, $sep-$j), $b2, 1, $align, $fill);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
            }
            else
                $i++;
        }
        if($this->ws>0)
        {
            $this->ws = 0;
            $this->_out('0 Tw');
        }
        if($border && (strpos($border, 'B')!==false))
            $b = $b or 2;
        $this->Cell($w, $h, substr($s, $j, $i), $b, 1, $align, $fill);
        $this->x = $this->lMargin;
    }

    function Write($h, $txt, $link='')
    {
        $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2*$this->cMargin)*1000/$this->FontSizePt;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                $this->Cell($w, $h, substr($s, $j, $i-$j), 0, 1, '', false, $link);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $this->CurrentFont['cw'][ord($c)];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    $this->Cell($w, $h, substr($s, $j, $i-$j), 0, 1, '', false, $link);
                }
                else
                {
                    $this->Cell($w, $h, substr($s, $j, $sep-$j), 0, 1, '', false, $link);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        if($i>$j)
            $this->Cell($l/1000*$this->FontSize, $h, substr($s, $j), 0, 0, '', false, $link);
    }

    function Ln($h=NULL)
    {
        $this->x = $this->lMargin;
        if(is_string($h))
            $this->y += $this->lasth;
        else
            $this->y += $h;
    }

    function SetX($x)
    {
        $this->x = $x;
    }

    function SetY($y)
    {
        $this->y = $y;
    }

    function SetXY($x, $y)
    {
        $this->SetX($x);
        $this->SetY($y);
    }

    function GetX()
    {
        return $this->x;
    }

    function GetY()
    {
        return $this->y;
    }

    function SetAutoPageBreak($auto, $margin=0)
    {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h - $margin;
    }

    function Image($file, $x=NULL, $y=NULL, $w=0, $h=0, $type='', $link='')
    {
    }

    function EndDoc()
    {
        $this->state = 3;
    }

    function Output($dest='', $name='')
    {
        $pdf = $this->_endpage();
        
        // Build PDF
        $out = "%PDF-1.3\n";
        $out .= "1 0 obj\n<< >>\nendobj\n";
        $out .= "xref\n0 1\n0000000000 65535 f \n";
        $out .= "trailer\n<< /Size 1 /Root 1 0 R >>\n";
        $out .= "startxref\n9\n%%EOF";
        
        if($dest=='')
            $dest = 'I';
        $dest = strtoupper($dest);
        if($dest=='I')
            echo $pdf;
        elseif($dest=='D')
        {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="'.addslashes($name).'"');
            echo $pdf;
        }
        elseif($dest=='F')
            file_put_contents($name, $pdf);
        elseif($dest=='S')
            return $pdf;
    }

    protected function _escape($s)
    {
        $s = str_replace('\\', '\\\\', $s);
        $s = str_replace('(', '\\(', $s);
        $s = str_replace(')', '\\)', $s);
        $s = str_replace("\r", '\\r', $s);
        return $s;
    }

    protected function _out($s)
    {
        if($this->state==2)
            $this->pages[$this->currentpage] .= $s."\n";
        else
            $this->buffer .= $s."\n";
    }

    protected function _endpage()
    {
        $this->state = 0;
        $out = "%PDF-1.3\n";
        foreach($this->pages as $p)
            $out .= $p;
        return $out;
    }
}
?>
