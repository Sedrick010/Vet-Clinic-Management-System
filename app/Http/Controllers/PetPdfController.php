<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Services\TenantDatabaseService;
use Illuminate\Http\Request;
require_once base_path('vendor/setasign/fpdf/fpdf.php');

// Extend FPDF to create custom header and footer
class PetPDF extends \FPDF
{
    private $clinic;
    
    function __construct($clinic)
    {
        parent::__construct();
        $this->clinic = $clinic;
    }
    
    // Page Header
    function Header()
    {
        // Add a light blue header background
        $this->SetFillColor(240, 248, 255); // Softer light blue
        $this->Rect(0, 0, 210, 45, 'F');
        
        // Add border line
        $this->SetDrawColor(130, 150, 180); // Softer steel blue
        $this->SetLineWidth(0.5);
        $this->Line(0, 45, 210, 45);
        
        // Try to load the clinic logo if it exists
        $logoPath = public_path('images/logos/default-clinic-logo.png');
        if(file_exists($logoPath)) {
            // Add logo image
            $this->Image($logoPath, 10, 8, 30, 30);
            $logoOffset = 45;
        } else {
            // Fallback to paw print icon
            $this->SetFont('Arial', 'B', 26);
            $this->SetTextColor(70, 130, 180); // Steel blue
            $this->SetXY(10, 15);
            $this->Cell(30, 15, '🐾', 0, 0, 'C');
            $logoOffset = 45;
        }
        
        // Clinic name - clean design with no shadow
        $this->SetFont('Arial', 'B', 24);
        $this->SetTextColor(80, 120, 160); // Softer blue for text
        $this->SetXY($logoOffset, 16);
        $this->Cell(160, 10, $this->clinic->name, 0, 1, 'L');
        
        // Clinic contact info
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(80, 80, 80); // Dark gray
        $this->SetXY($logoOffset, 28);
        $this->Cell(160, 5, $this->clinic->address, 0, 1, 'L');
        $this->SetXY($logoOffset, 34);
        $this->Cell(160, 5, 'Phone: ' . $this->clinic->phone, 0, 1, 'L');
        
        // Space after header
        $this->Ln(10);
    }
    
    // Page Footer
    function Footer()
    {
        // Set position 15mm from bottom
        $this->SetY(-30);
        
        // Add border line
        $this->SetDrawColor(70, 130, 180); // Steel blue
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
        
        // Add mini logo in footer
        $logoPath = public_path('images/logos/default-clinic-logo.png');
        if(file_exists($logoPath)) {
            $this->Image($logoPath, 10, $this->GetY() - 2, 8, 8);
            $this->SetX(22);
        }
        
        // Add footer text
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100); // Dark gray
        $this->Cell(0, 5, 'This document was generated on ' . date('F d, Y h:i A'), 0, 1, 'C');
        $this->Cell(0, 5, 'For any inquiries, please contact us at ' . $this->clinic->phone, 0, 1, 'C');
        
        // Add page number with simple text
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(70, 130, 180);
        $this->Cell(0, 5, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C');
    }
    
    // Method to create section title
    function SectionTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(80, 120, 160); // Softer blue
        $this->SetFillColor(240, 248, 255); // Softer light blue
        $this->Cell(0, 10, $title, 0, 1, 'L', true);
        $this->Ln(2);
    }
    
    // Draw a paw decoration
    function DrawPaw($x, $y, $size = 10)
    {
        $this->SetDrawColor(200, 200, 200);
        $this->SetFillColor(230, 230, 230);
        
        // Main pad
        $this->Ellipse($x, $y, $size * 0.8, $size * 0.6, 'F');
        
        // Toe pads
        $this->Ellipse($x - $size * 0.6, $y - $size * 0.6, $size * 0.4, $size * 0.3, 'F');
        $this->Ellipse($x, $y - $size * 0.7, $size * 0.4, $size * 0.3, 'F');
        $this->Ellipse($x + $size * 0.6, $y - $size * 0.6, $size * 0.4, $size * 0.3, 'F');
    }
    
    // Draw an ellipse
    function Ellipse($x, $y, $rx, $ry, $style = 'D')
    {
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' || $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
            
        $lx = 4/3 * (sqrt(2) - 1) * $rx;
        $ly = 4/3 * (sqrt(2) - 1) * $ry;
        
        $k = $this->k;
        $h = $this->h;
        
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x)*$k, ($h-$y)*$k,
            ($x+$lx)*$k, ($h-$y)*$k,
            ($x+$rx)*$k, ($h-$y+$ly)*$k,
            ($x+$rx)*$k, ($h-$y+$ry)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$rx)*$k, ($h-$y+$ry+$ly)*$k,
            ($x+$lx)*$k, ($h-$y+$ry*2)*$k,
            ($x)*$k, ($h-$y+$ry*2)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$lx)*$k, ($h-$y+$ry*2)*$k,
            ($x-$rx)*$k, ($h-$y+$ry+$ly)*$k,
            ($x-$rx)*$k, ($h-$y+$ry)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x-$rx)*$k, ($h-$y+$ly)*$k,
            ($x-$lx)*$k, ($h-$y)*$k,
            ($x)*$k, ($h-$y)*$k,
            $op));
    }

    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
    
    // Get appropriate icon for the pet species
    function getPetIcon($species)
    {
        $petIcon = 'Pets';
        
        if (strtolower($species) == 'dog') {
            $petIcon = 'Dog';
        } elseif (strtolower($species) == 'cat') {
            $petIcon = 'Cat';
        } elseif (strtolower($species) == 'bird') {
            $petIcon = 'Bird';
        } elseif (strtolower($species) == 'reptile') {
            $petIcon = 'Reptile';
        } elseif (strtolower($species) == 'rabbit') {
            $petIcon = 'Rabbit';
        } elseif (strtolower($species) == 'fish') {
            $petIcon = 'Fish';
        }
        
        return $petIcon;
    }
}

class PetPdfController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    private function getClinic(Request $request)
    {
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        $subdomain = null;

        if ($host !== $appDomain && str_contains($host, $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
        }

        if ($subdomain) {
            return \App\Models\Clinic::where('subdomain', $subdomain)->first();
        }

        return null;
    }

    public function generatePdf(Request $request, $id)
    {
        // Get the clinic and switch to tenant database
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get the pet with owner information
        $pet = Pet::with('owner')->findOrFail($id);
        
        // Create PDF with clinic data
        $pdf = new PetPDF($clinic);
        $pdf->AliasNbPages(); // For page numbering
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 30); // 30mm bottom margin
        
        // Set default colors
        $primaryColor = [70, 130, 180]; // Softer blue
        $lightBackground = [248, 250, 252]; // Light background
        
        // Add pet title with styled header
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Rect(0, 45, 210, 15, 'F');
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetY(48);
        $pdf->Cell(0, 8, 'Pet Medical Record', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->SetY(58);
        $pdf->Cell(0, 5, 'Generated on ' . now()->format('F d, Y'), 0, 1, 'C');
        $pdf->Ln(8);
        
        // Add decorative element
        $pdf->DrawPaw(180, 60, 8);
        
        // Pet icon and name header
        $petIconInfo = $pdf->getPetIcon($pet->species);
        $pdf->SetFillColor($lightBackground[0], $lightBackground[1], $lightBackground[2]);
        $pdf->RoundedRect(10, $pdf->GetY(), 190, 20, 4, 'F');
        
        // Pet name with styled color
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetXY(20, $pdf->GetY() + 5);
        $pdf->Cell(150, 10, $pet->name . ' (' . $petIconInfo . ')', 0, 1);
        
        $pdf->Ln(8);
        
        // Pet Information section
        $pdf->SectionTitle('Pet Information');
        
        $leftCol = 40;
        $rightCol = 40;
        $leftX = 15;
        $rightX = 110;
        
        // Create a sophisticated box with rounded corners
        $startY = $pdf->GetY();
        $pdf->SetFillColor(248, 250, 252);
        $pdf->RoundedRect(10, $startY, 190, 35, 5, 'F');
        
        // Add subtle border
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->RoundedRect(10, $startY, 190, 35, 5);
        
        // Set position for content
        $pdf->SetY($startY + 5);
        
        // Row 1
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetX($leftX);
        $pdf->Cell($leftCol, 8, 'Species:', 0, 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->Cell($rightCol, 8, $pet->species, 0, 0);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetX($rightX);
        $pdf->Cell($leftCol, 8, 'Breed:', 0, 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->Cell($rightCol, 8, $pet->breed ?: 'Not specified', 0, 1);
        
        // Row 2
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetX($leftX);
        $pdf->Cell($leftCol, 8, 'Gender:', 0, 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->Cell($rightCol, 8, ucfirst($pet->gender ?: 'Not specified'), 0, 0);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetX($rightX);
        $pdf->Cell($leftCol, 8, 'Age:', 0, 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        // Get age from birthdate or use the age property if available
        $ageText = 'Not specified';
        if ($pet->birthdate) {
            $ageText = $pet->birthdate->age . ' years';
        } elseif (isset($pet->age) && $pet->age) {
            $ageText = $pet->age . ' years';
        }
        $pdf->Cell($rightCol, 8, $ageText, 0, 1);
        
        // Row 3
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetX($leftX);
        $pdf->Cell($leftCol, 8, 'Weight:', 0, 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->Cell($rightCol, 8, $pet->weight ? $pet->weight . ' kg' : 'Not recorded', 0, 0);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetX($rightX);
        $pdf->Cell($leftCol, 8, 'Color:', 0, 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->Cell($rightCol, 8, $pet->color ?: 'Not specified', 0, 1);
        
        $pdf->Ln(12);
        
        // Owner Information
        $pdf->SectionTitle('Owner Information');
        
        if($pet->owner) {
            // Create a subtle background for owner info
            $startY = $pdf->GetY();
            $pdf->SetFillColor(245, 250, 255); // Slightly different background
            $pdf->RoundedRect(10, $startY, 190, 40, 5, 'F');
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->RoundedRect(10, $startY, 190, 40, 5);
            
            // Set position for content
            $pdf->SetY($startY + 5);
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($leftX);
            $pdf->Cell($leftCol, 8, 'Name:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(70, 70, 70);
            $pdf->Cell($rightCol, 8, $pet->owner->name, 0, 1);
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($leftX);
            $pdf->Cell($leftCol, 8, 'Phone:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(70, 70, 70);
            $pdf->Cell($rightCol, 8, $pet->owner->phone, 0, 1);
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($leftX);
            $pdf->Cell($leftCol, 8, 'Email:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(70, 70, 70);
            $pdf->Cell($rightCol, 8, $pet->owner->email ?: 'Not provided', 0, 1);
            
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($leftX);
            $pdf->Cell($leftCol, 8, 'Address:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(70, 70, 70);
            $pdf->Cell($rightCol, 8, $pet->owner->address ?: 'Not provided', 0, 1);
        } else {
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetX($leftX);
            $pdf->Cell(0, 8, 'No owner information available', 0, 1);
        }
        
        $pdf->Ln(12);
        
        // Medical Information section if available
        if ($pet->medical_history || $pet->allergies || $pet->current_medications) {
            $pdf->SectionTitle('Medical Information');
            
            // Create a container for medical info
            $startY = $pdf->GetY();
            $boxHeight = 15; // Base height
            
            // Calculate needed height based on content
            if ($pet->medical_history) $boxHeight += 20;
            if ($pet->allergies) $boxHeight += 20;
            if ($pet->current_medications) $boxHeight += 20;
            
            $pdf->SetFillColor(250, 250, 255);
            $pdf->RoundedRect(10, $startY, 190, $boxHeight, 5, 'F');
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->RoundedRect(10, $startY, 190, $boxHeight, 5);
            
            // Set position for content
            $pdf->SetY($startY + 5);
            
            if ($pet->medical_history) {
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetX($leftX);
                $pdf->Cell(0, 8, 'Medical History:', 0, 1);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(70, 70, 70);
                $pdf->SetX($leftX);
                $pdf->MultiCell(180, 6, $pet->medical_history, 0, 'L');
                $pdf->Ln(3);
            }
            
            if ($pet->allergies) {
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetX($leftX);
                $pdf->Cell(0, 8, 'Allergies:', 0, 1);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(70, 70, 70);
                $pdf->SetX($leftX);
                $pdf->MultiCell(180, 6, $pet->allergies, 0, 'L');
                $pdf->Ln(3);
            }
            
            if ($pet->current_medications) {
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetX($leftX);
                $pdf->Cell(0, 8, 'Current Medications:', 0, 1);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(70, 70, 70);
                $pdf->SetX($leftX);
                $pdf->MultiCell(180, 6, $pet->current_medications, 0, 'L');
            }
        }
        
        // Output the PDF
        return $pdf->Output('D', 'Pet_' . $pet->name . '.pdf');
    }

    public function generateAllPetsPdf(Request $request)
    {
        // Get the clinic and switch to tenant database
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get all pets with owner information
        $pets = Pet::with('owner')->orderBy('species')->orderBy('name')->get();
        
        // Group pets by species
        $petsBySpecies = $pets->groupBy('species');
        
        // Create PDF with clinic data
        $pdf = new PetPDF($clinic);
        $pdf->AliasNbPages(); // For page numbering
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 30); // 30mm bottom margin
        
        // Set default colors - Softer, less saturated colors that are easier on the eyes
        $primaryColor = [70, 130, 180]; // Softer blue (steel blue)
        $secondaryColor = [128, 137, 145]; // Softer secondary
        $lightBackground = [248, 250, 252]; // Light background (kept as is, already soft)
        
        // Add pets title with styled header
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Rect(0, 45, 210, 15, 'F');
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetY(48);
        $pdf->Cell(0, 8, 'Pets Registry', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->SetY(58);
        $pdf->Cell(0, 5, 'Generated on ' . now()->format('F d, Y'), 0, 1, 'C');
        $pdf->Ln(8);
        
        // Summary section with styled box
        $pdf->SetFillColor($lightBackground[0], $lightBackground[1], $lightBackground[2]);
        $pdf->RoundedRect(10, $pdf->GetY(), 190, 20, 4, 'F');
        
        // Summary section header
        $pdf->SetY($pdf->GetY() + 3);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(0, 7, '  Summary', 0, 1, 'L');
        
        // Add pets count summary
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(60, 7, '  Total Pets: ' . $pets->count(), 0, 1);
        
        // Add pet categories with colored indicators
        $pdf->Ln(8);
        
        // Pet categories section with styled box
        $pdf->SetFillColor($lightBackground[0], $lightBackground[1], $lightBackground[2]);
        $pdf->RoundedRect(10, $pdf->GetY(), 190, 8 + ceil($petsBySpecies->count() / 2) * 7, 4, 'F');
        
        $pdf->SetY($pdf->GetY() + 3);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(0, 6, '  Pet Categories', 0, 1, 'L');
        
        // Create color indicators in two columns
        $categoryX = 20;
        $categoryY = $pdf->GetY();
        $count = 0;
        
        foreach ($petsBySpecies as $species => $speciesPets) {
            $speciesIconInfo = $pdf->getPetIcon($species);
            
            // Switch to second column after half the categories
            if ($count >= ceil($petsBySpecies->count() / 2)) {
                $categoryX = 110;
                $pdf->SetY($categoryY + ($count - ceil($petsBySpecies->count() / 2)) * 7);
            } else {
                $pdf->SetY($categoryY + $count * 7);
            }
            
            // Remove color indicator box and just use text
            $pdf->SetX($categoryX);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->Cell(70, 5, ucfirst($species) . ': ' . count($speciesPets), 0, 0);
            
            $count++;
        }
        
        // Space after categories
        $pdf->SetY($categoryY + ceil($petsBySpecies->count() / 2) * 7 + 8);
        
        // Pets list header
        $pdf->SetDrawColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(0, 10, 'Detailed Pet Information', 0, 1, 'C');
        
        // Process each species group
        foreach ($petsBySpecies as $species => $speciesPets) {
            // Get species icon and color
            $speciesIconInfo = $pdf->getPetIcon($species);
            
            // Species header
            $pdf->SetDrawColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetLineWidth(0.3);
            $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->Cell(0, 10, ucfirst($species) . ': ' . count($speciesPets), 0, 1, 'L');
            
            // Loop through each pet in this species
            $petNumber = 1;
            foreach ($speciesPets as $pet) {
                // Create a background container for the pet
                $sectionStartY = $pdf->GetY();
                $pdf->SetFillColor($lightBackground[0], $lightBackground[1], $lightBackground[2]);
                $pdf->RoundedRect(10, $sectionStartY, 190, 50, 4, 'F');
                $pdf->SetDrawColor(220, 220, 220);
                $pdf->RoundedRect(10, $sectionStartY, 190, 50, 4, 'D');
                
                // Pet header with colored bar
                $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->RoundedRect(10, $sectionStartY, 190, 10, 4, 'F');
                
                // Pet number and name
                $pdf->SetY($sectionStartY + 2);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(10, 7, "  ", 0, 0);
                $pdf->Cell(165, 7, "Pet #" . $petNumber . ": " . $pet->name, 0, 1, 'L');
                
                $pdf->SetY($sectionStartY + 15);
                
                // Left and right columns
                $leftCol = 40;
                $rightCol = 40;
                $leftX = 15;
                $rightX = 110;
                
                // Details
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetX($leftX);
                $pdf->Cell($leftCol, 7, 'Species:', 0, 0);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(70, 70, 70);
                $pdf->Cell($rightCol, 7, $pet->species, 0, 0);
                
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetX($rightX);
                $pdf->Cell($leftCol, 7, 'Breed:', 0, 0);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(70, 70, 70);
                $pdf->Cell($rightCol, 7, $pet->breed ?: 'Not specified', 0, 1);
                
                // Row 2
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetX($leftX);
                $pdf->Cell($leftCol, 7, 'Gender:', 0, 0);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(70, 70, 70);
                $pdf->Cell($rightCol, 7, ucfirst($pet->gender ?: 'Not specified'), 0, 0);
                
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetX($rightX);
                $pdf->Cell($leftCol, 7, 'Age:', 0, 0);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(70, 70, 70);
                // Get age from birthdate or use the age property if available
                $ageText = 'Not specified';
                if ($pet->birthdate) {
                    $ageText = $pet->birthdate->age . ' years';
                } elseif (isset($pet->age) && $pet->age) {
                    $ageText = $pet->age . ' years';
                }
                $pdf->Cell($rightCol, 7, $ageText, 0, 1);
                
                // Owner info
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->SetX($leftX);
                $pdf->Cell($leftCol, 7, 'Owner:', 0, 0);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(70, 70, 70);
                if ($pet->owner) {
                    $pdf->Cell($rightCol, 7, $pet->owner->name, 0, 0);
                    
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                    $pdf->SetX($rightX);
                    $pdf->Cell($leftCol, 7, 'Contact:', 0, 0);
                    
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->SetTextColor(70, 70, 70);
                    $pdf->Cell($rightCol, 7, $pet->owner->phone, 0, 1);
                } else {
                    $pdf->Cell($rightCol, 7, 'Not assigned', 0, 1);
                }
                
                // Spacing after pet
                $pdf->Ln(8);
                $petNumber++;
                
                // Add a page break if needed
                if ($pet != $speciesPets->last()) {
                    // Check if we're close to the bottom of the page
                    if ($pdf->GetY() > 230) {
                        $pdf->AddPage();
                    }
                }
            }
            
            // Add a page break between species if not the last species
            $speciesKeys = array_keys($petsBySpecies->toArray());
            $lastSpecies = end($speciesKeys);
            if ($species !== $lastSpecies) {
                $pdf->AddPage();
            }
        }
        
        // Output the PDF
        return $pdf->Output('D', 'All_Pets.pdf');
    }
} 