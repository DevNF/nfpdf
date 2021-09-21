<?php

namespace NFPDF;

use Codedge\Fpdf\Fpdf\Fpdf as FPDF;

class NFPDF extends FPDF
{
var $widths;
var $aligns;
var $headerRow;
var $headerFont;
var $headerWidth;
var $angulo;
var $border;
var $height;

function SetHeight($h = null)
{
    //Set the array of column widths
    if (!empty($h)) {
        $this->height=$h;
    }else {
        $this->height='';
    }
}

function SetWidths($w)
{
    //Set the array of column widths
    $this->widths=$w;
}

function SetAligns($a)
{
    //Set the array of column alignments
    $this->aligns=$a;
}

function SetNoBorder($b)
{
    //Set the array of column alignments
    $this->border=$b;
}

function Girar($angulo=0,$x=-1,$y=-1)
{
    if($x==-1) $x=$this->x;

    if($y==-1) $y=$this->y;

    if($this->angulo!=0) $this->_out('Q');

    $this->angulo=$angulo;
    if($angulo!=0)
    {
        $angulo*=M_PI/180;
        $c=cos($angulo);
        $s=sin($angulo);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;

        $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
    }
}

function Row($data, $colors = array(), $angulo = null, $recuo = null)
{
    //Calculate the height of the row
    $nb=0;
    for($i=0;$i<count($data);$i++)
    {
        $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        if (((@isset($this->widths[$i]) && @!isset($data[$i])) || (@isset($data[$i]) && @!isset($this->widths[$i]))) && $data[$i] != null) {
            var_dump($this->widths);
            var_dump($data);
            var_dump($i);
            var_dump(isset($this->widths[$i]));
            var_dump(isset($data[$i]));
        }
    }
    $h=5*$nb;
    //Issue a page break first if needed
    $this->CheckPageBreak($h);
    //Draw the cells of the row
    if (!empty($recuo)) {
        $h -= $recuo;
    }
    for($i=0;$i<count($data);$i++)
    {
        $w=$this->widths[$i];
        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
        //Save the current position
        $x=$this->GetX();
        $y=$this->GetY();
        //Draw the border
        if (empty($this->border)) {
            $this->Rect($x,$y,$w,$h);
        }
        //Print the text
        $this->SetTextColor(0, 0, 0);
        if (!empty($colors[$i])) {
            $this->SetTextColor($colors[$i][0], $colors[$i][1], $colors[$i][2]);
        }
        if (!empty($angulo)) {
            $this->Girar($angulo);
            $alinhamentoY = $y - ($w / 2);
            $alinhamentoX = $x + ($h / 3);
            if (!empty($recuo)) {
                $alinhamentoX -= ($recuo/2);
            }
            $this->Text($alinhamentoX, $alinhamentoY, $data[$i]);
            $this->Girar(0);
        } else {
            if (!empty($this->height)) {
                $this->MultiCell($w,$this->height[$i],$data[$i],0,$a);
            } else {
                $this->MultiCell($w,5,$data[$i],0,$a);
            }
        }
        //Put the position to the right of the cell
        $this->SetXY($x+$w,$y);
    }
    //Go to the next line
    $this->Ln($h);
}

function CheckPageBreak($h)
{
    //If the height h would cause an overflow, add a new page immediately
    if($this->GetY()+$h>$this->PageBreakTrigger){
      $this->AddPage($this->CurOrientation);
      if ($this->headerRow) {
        $family = $this->FontFamily;
        $style = $this->FontStyle;
        $size = $this->FontSizePt;
        $this->SetFont($this->headerFont[0], $this->headerFont[1], $this->headerFont[2]);
        if (!empty($this->headerRow) && !empty($this->headerWidth)) {
            $old = $this->widths;
            $this->SetWidths($this->headerWidth);
        }
        $this->Row($this->headerRow);
        $this->Ln(5);
        $this->SetFont($family, $style, $size);
        if (!empty($old)) {
            $this->SetWidths($old);
        }

      }
    }
}

function NbLines($w,$txt)
{
    //Computes the number of lines a MultiCell of width w will take
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $nl=1;
    while($i<$nb)
    {
        $c=$s[$i];
        if($c=="\n")
        {
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
            continue;
        }
        if($c==' ')
            $sep=$i;
        $l+=$cw[$c];
        if($l>$wmax)
        {
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
            }
            else
                $i=$sep+1;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
        }
        else
            $i++;
    }
    return $nl;
}
}
?>
