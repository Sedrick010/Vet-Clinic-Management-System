<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\TenantDatabaseService;
use Illuminate\Http\Request;
require_once base_path('vendor/setasign/fpdf/fpdf.php');

// Extend FPDF to create custom header and footer
class AppointmentPDF extends \FPDF
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
        // Add a light blue header background, but cleaner, no gradient
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
        $this->SetXY($logoOffset, 16); // Remove the +1 shadow offset
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
        
        // Add page number with simple text instead of decorative elements
        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(70, 130, 180);
        $this->Cell(0, 5, 'Page ' . $this->PageNo() . ' of {nb}', 0, 0, 'C'); // Removed decorative ◆ symbols
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
    
    // Method to create a field with label and value
    function InfoField($label, $value)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(80, 80, 80);
        $this->Cell(40, 7, $label . ':', 0);
        
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 7, $value, 0, 1);
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
    
    // Add a watermark to each page
    /*
    function AddWatermark()
    {
        // Save the current state
        $this->_out('q');
        
        // Watermark text
        $text = $this->clinic->name . ' Veterinary Clinic';
        
        // Set font and color for watermark
        $this->SetFont('Arial', 'B', 50);
        $this->SetTextColor(230, 230, 230);
        
        // Rotate 45 degrees and position the watermark
        $this->_out(sprintf('BT %.2F %.2F %.2F %.2F %.2F %.2F Tm',
            cos(deg2rad(45)), sin(deg2rad(45)), -sin(deg2rad(45)), cos(deg2rad(45)),
            105, 120)); // Position
        
        // Output the text
        $this->_out(sprintf('(%s) Tj', $this->_escape($text)));
        $this->_out('ET');
        
        // Restore the state
        $this->_out('Q');
    }
    */

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
}

class AppointmentPdfController extends Controller
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

        // Direct check for subscription plan to prevent middleware bypass
        if ($clinic->subscription_plan === 'free' || !$clinic->is_subscription_active) {
            return redirect()->route('subscription.index')
                ->with('upgrade_required', true)
                ->with('error', 'PDF export is only available on paid plans (Basic, Standard, and Business). Please upgrade your subscription to access this feature.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get the appointment with related data
        $appointment = Appointment::with(['staff', 'pet'])->findOrFail($id);
        
        // Create PDF with clinic data
        $pdf = new AppointmentPDF($clinic);
        $pdf->AliasNbPages(); // For page numbering
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 30); // 30mm bottom margin
        
        // Remove or comment out the watermark call
        // $pdf->AddWatermark();
        
        // Add appointment title
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(0, 10, 'Appointment Details', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Appointment #' . $appointment->id, 0, 1, 'C');
        
        // Add decorative element
        $pdf->DrawPaw(180, 60, 8);
        $pdf->Ln(10);
        
        // Client and Staff Information section
        $pdf->SectionTitle('Client & Staff Information');
        
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(70, 130, 180);
        $pdf->Cell(95, 8, 'Client', 0);
        $pdf->Cell(95, 8, 'Veterinarian', 0, 1);
        
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(95, 8, $appointment->client_name, 0);
        $pdf->Cell(95, 8, $appointment->staff->name, 0, 1);
        $pdf->Ln(5);
        
        // Pet Information section
        if ($appointment->pet) {
            $pdf->SectionTitle('Pet Information');
            
            // Create a rounded box for pet info with subtle background
            $pdf->SetFillColor(240, 250, 255);
            $startY = $pdf->GetY();
            $pdf->RoundedRect(15, $startY, 180, 20, 5, 'F');
            
            // Get appropriate icon for the pet species
            $petIcon = '🐾';
            $iconColor = [70, 130, 180]; // Default blue
            
            if (strtolower($appointment->pet->species) == 'dog') {
                $petIcon = '🐕';
                $iconColor = [121, 85, 72]; // Brown for dogs
            } elseif (strtolower($appointment->pet->species) == 'cat') {
                $petIcon = '🐈';
                $iconColor = [96, 125, 139]; // Blue-grey for cats
            } elseif (strtolower($appointment->pet->species) == 'bird') {
                $petIcon = '🐦';
                $iconColor = [0, 150, 136]; // Teal for birds
            } elseif (strtolower($appointment->pet->species) == 'reptile') {
                $petIcon = '🦎';
                $iconColor = [46, 125, 50]; // Green for reptiles
            } elseif (strtolower($appointment->pet->species) == 'rabbit') {
                $petIcon = '🐇';
                $iconColor = [156, 39, 176]; // Purple for rabbits
            } elseif (strtolower($appointment->pet->species) == 'fish') {
                $petIcon = '🐠';
                $iconColor = [3, 169, 244]; // Light blue for fish
            }
            
            // Add pet icon with color
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetTextColor($iconColor[0], $iconColor[1], $iconColor[2]);
            $pdf->SetXY(20, $startY + 5);
            $pdf->Cell(15, 10, $petIcon, 0, 0);
            
            // Pet name with species-specific color
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetTextColor($iconColor[0], $iconColor[1], $iconColor[2]);
            $pdf->SetXY(35, $startY + 5);
            $pdf->Cell(50, 10, $appointment->pet->name, 0, 0);
            
            // Pet details
            $pdf->SetFont('Arial', '', 11);
            $pdf->SetTextColor(80, 80, 80);
            $petDetails = '(' . $appointment->pet->species;
            
            if ($appointment->pet->breed) {
                $petDetails .= ' - ' . $appointment->pet->breed;
            }
            
            $petDetails .= ', ' . ucfirst($appointment->pet->gender ?? 'Unknown') . ')';
            
            $pdf->SetXY(90, $startY + 5);
            $pdf->Cell(100, 10, $petDetails, 0, 0);
            
            $pdf->Ln(25);
        }
        
        // Appointment Time and Status section
        $pdf->SectionTitle('Appointment Details');
        
        // Create a sophisticated box with rounded corners and minimal shadow
        $startY = $pdf->GetY();
        $pdf->SetFillColor(248, 250, 252);
        $pdf->RoundedRect(15, $startY, 180, 40, 5, 'F');
        
        // Remove multiple shadow lines and just use a single subtle line
        $pdf->SetDrawColor(220, 220, 220); // Lighter gray for cleaner look
        $pdf->RoundedRect(15, $startY, 180, 40, 5); // Just one outline, no multiple shadow lines
        
        // Set position for content
        $pdf->SetY($startY + 5);
        
        // Create a grid-like structure for appointment details
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(70, 130, 180); // Steel blue
        $pdf->SetX(25);
        $pdf->Cell(40, 8, 'Date:', 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(50, 8, $appointment->start_time->format('F d, Y'), 0);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(70, 130, 180); // Steel blue
        $pdf->Cell(30, 8, 'Status:', 0);
        
        // Status with colored box
        // Change color based on status
        switch($appointment->status) {
            case 'scheduled':
                $pdf->SetFillColor(135, 206, 250); // Light blue
                break;
            case 'completed':
                $pdf->SetFillColor(144, 238, 144); // Light green
                break;
            case 'cancelled':
                $pdf->SetFillColor(255, 160, 160); // Light red
                break;
            case 'no-show':
                $pdf->SetFillColor(255, 220, 165); // Light orange
                break;
            default:
                $pdf->SetFillColor(220, 220, 220); // Light grey
        }
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(40, 8, ucfirst($appointment->status), 0, 1, 'C', true);
        
        // Time details
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(70, 130, 180); // Steel blue
        $pdf->SetX(25);
        $pdf->Cell(40, 8, 'Start Time:', 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(50, 8, $appointment->start_time->format('h:i A'), 0, 0);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(70, 130, 180); // Steel blue
        $pdf->Cell(30, 8, 'End Time:', 0);
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(40, 8, $appointment->end_time->format('h:i A'), 0, 1);
        
        // Duration
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(70, 130, 180); // Steel blue
        $pdf->SetX(25);
        $pdf->Cell(40, 8, 'Duration:', 0);
        
        // Calculate duration in minutes
        $duration = $appointment->start_time->diffInMinutes($appointment->end_time);
        $durationText = $duration . ' minutes';
        if($duration >= 60) {
            $hours = floor($duration / 60);
            $mins = $duration % 60;
            $durationText = $hours . ' hour' . ($hours > 1 ? 's' : '');
            if($mins > 0) {
                $durationText .= ' ' . $mins . ' minute' . ($mins > 1 ? 's' : '');
            }
        }
        
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->Cell(140, 8, $durationText, 0, 1);
        
        $pdf->Ln(15);
        
        // Reason for Visit section
        $pdf->SectionTitle('Reason for Visit');
        $pdf->SetFont('Arial', '', 11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 7, $appointment->reason, 0, 'L');
        $pdf->Ln(5);
        
        // Additional Notes section (if available)
        if ($appointment->notes) {
            $pdf->SectionTitle('Additional Notes');
            $pdf->SetFont('Arial', '', 11);
            $pdf->MultiCell(0, 7, $appointment->notes, 0, 'L');
            $pdf->Ln(5);
        }
        
        // Output the PDF
        return $pdf->Output('D', 'Appointment_' . $appointment->id . '.pdf');
    }

    public function generateAllAppointmentsPdf(Request $request)
    {
        // Get the clinic and switch to tenant database
        $clinic = $this->getClinic($request);
        if (!$clinic) {
            return redirect()->route('login')
                ->with('error', 'No clinic selected. Please login again.');
        }

        // Direct check for subscription plan to prevent middleware bypass
        if ($clinic->subscription_plan === 'free' || !$clinic->is_subscription_active) {
            return redirect()->route('subscription.index')
                ->with('upgrade_required', true)
                ->with('error', 'PDF export is only available on paid plans (Basic, Standard, and Business). Please upgrade your subscription to access this feature.');
        }

        $this->tenantDatabaseService->switchToTenant($clinic);

        // Get appointments with related data
        $appointments = Appointment::with(['staff', 'pet'])
            ->orderBy('start_time', 'desc')
            ->get();
        
        // Create PDF with clinic data
        $pdf = new AppointmentPDF($clinic);
        $pdf->AliasNbPages(); // For page numbering
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 30); // 30mm bottom margin
        
        // Set default colors - Softer, less saturated colors that are easier on the eyes
        $primaryColor = [70, 130, 180]; // Softer blue (steel blue)
        $secondaryColor = [128, 137, 145]; // Softer secondary
        $lightBackground = [248, 250, 252]; // Light background (kept as is, already soft)
        $successColor = [76, 175, 80]; // Softer green
        $dangerColor = [188, 84, 94]; // Softer red
        $warningColor = [240, 173, 78]; // Softer orange
        $infoColor = [70, 170, 185]; // Softer teal
        
        // Add appointments title with styled header
        $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Rect(0, 45, 210, 15, 'F');
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetY(48);
        $pdf->Cell(0, 8, 'Appointments List', 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->SetY(58);
        $pdf->Cell(0, 5, 'Generated on ' . now()->format('F d, Y'), 0, 1, 'C');
        $pdf->Ln(12);
        
        // Summary section with styled box
        $pdf->SetFillColor($lightBackground[0], $lightBackground[1], $lightBackground[2]);
        $pdf->RoundedRect(10, $pdf->GetY(), 190, 35, 4, 'F');
        
        // Summary section header
        $pdf->SetY($pdf->GetY() + 3);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(0, 8, '  Summary', 0, 1, 'L');
        
        // Add appointment count summary
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Cell(60, 8, '  Total Appointments: ' . $appointments->count(), 0, 1);
        
        // Status breakdown without colored indicators - use only blue headers
        $statusCounts = $appointments->groupBy('status')->map->count();
        
        // Add appointment stats without color indicators
        $pdf->Ln(5);
        $pdf->SetTextColor(70, 70, 70);
        $pdf->SetFont('Arial', '', 10);
        
        // Display status counts without color indicators
        $pdf->SetX(20);
        $pdf->Cell(40, 5, 'Scheduled: ' . ($statusCounts['scheduled'] ?? 0), 0, 0);
        $pdf->Cell(40, 5, 'Completed: ' . ($statusCounts['completed'] ?? 0), 0, 0);
        $pdf->Cell(40, 5, 'Cancelled: ' . ($statusCounts['cancelled'] ?? 0), 0, 0);
        $pdf->Cell(40, 5, 'No-show: ' . ($statusCounts['no-show'] ?? 0), 0, 1);
        
        $pdf->Ln(18);
        
        // Detailed appointments section header
        $pdf->SetDrawColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
        $pdf->Cell(0, 12, 'Detailed Appointment Information', 0, 1, 'C');
        $pdf->SetLineWidth(0.2);
        
        // Loop through each appointment and create a detailed section
        $appointmentNumber = 1;
        foreach ($appointments as $appointment) {
            // Create a background container for the appointment
            $sectionStartY = $pdf->GetY();
            $pdf->SetFillColor($lightBackground[0], $lightBackground[1], $lightBackground[2]);
            $pdf->RoundedRect(10, $sectionStartY, 190, 125, 4, 'F');
            $pdf->SetDrawColor(220, 220, 220);
            $pdf->RoundedRect(10, $sectionStartY, 190, 125, 4, 'D');
            
            // Appointment header with blue header for all statuses
            $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->RoundedRect(10, $sectionStartY, 190, 10, 4, 'F');
            
            // Appointment number and status
            $pdf->SetY($sectionStartY + 2);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(150, 7, "  Appointment #" . $appointmentNumber, 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(40, 7, ucfirst($appointment->status), 0, 1, 'R');
            
            $pdf->SetY($sectionStartY + 17);
            
            // Left and right columns for appointment info
            $leftX = 15;
            $rightX = 110;
            $pdf->SetTextColor(60, 60, 60);
            
            // Client and Doctor - left column
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($leftX);
            $pdf->Cell(30, 8, 'Client:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(60, 8, $appointment->client_name, 0, 0);
            
            // Doctor - right column
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($rightX);
            $pdf->Cell(30, 8, 'Doctor:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(60, 8, $appointment->staff->name, 0, 1);
            
            // Date - left column
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($leftX);
            $pdf->Cell(30, 8, 'Date:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(60, 8, $appointment->start_time->format('F d, Y'), 0, 0);
            
            // Start Time - right column
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($rightX);
            $pdf->Cell(30, 8, 'Start Time:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(60, 8, $appointment->start_time->format('h:i A'), 0, 1);
            
            // End time - left column
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($leftX);
            $pdf->Cell(30, 8, 'End Time:', 0, 0);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(60, 8, $appointment->end_time->format('h:i A'), 0, 0);
            
            // Duration - right column
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX($rightX);
            $pdf->Cell(30, 8, 'Duration:', 0, 0);
            
            // Calculate duration in minutes
            $duration = $appointment->start_time->diffInMinutes($appointment->end_time);
            $durationText = $duration . ' minutes';
            if($duration >= 60) {
                $hours = floor($duration / 60);
                $mins = $duration % 60;
                $durationText = $hours . ' hour' . ($hours > 1 ? 's' : '');
                if($mins > 0) {
                    $durationText .= ' ' . $mins . ' minute' . ($mins > 1 ? 's' : '');
                }
            }
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->Cell(60, 8, $durationText, 0, 1);
            
            // Pet information section
            $pdf->Ln(5);
            $pdf->SetX($leftX);
            $pdf->SetFillColor(235, 248, 250); // Softer light blue background
            $pdf->RoundedRect($leftX - 5, $pdf->GetY(), 180, 35, 2, 'F');
            
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor($infoColor[0], $infoColor[1], $infoColor[2]);
            $pdf->Cell(0, 8, 'Pet Information', 0, 1, 'L');
            
            if ($appointment->pet) {
                // Pet name and species
                $pdf->SetX($leftX);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->Cell(30, 8, 'Pet Name:', 0, 0);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(60, 60, 60);
                $pdf->Cell(60, 8, $appointment->pet->name, 0, 0);
                
                $pdf->SetX($rightX);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->Cell(30, 8, 'Species:', 0, 0);
                
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(60, 60, 60);
                $pdf->Cell(60, 8, $appointment->pet->species, 0, 1);
                
                // Breed and gender
                if ($appointment->pet->breed || $appointment->pet->gender) {
                    $pdf->SetX($leftX);
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                    $pdf->Cell(30, 8, 'Breed:', 0, 0);
                    
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->SetTextColor(60, 60, 60);
                    $pdf->Cell(60, 8, $appointment->pet->breed ?? 'Not specified', 0, 0);
                    
                    $pdf->SetX($rightX);
                    $pdf->SetFont('Arial', 'B', 10);
                    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                    $pdf->Cell(30, 8, 'Gender:', 0, 0);
                    
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->SetTextColor(60, 60, 60);
                    $pdf->Cell(60, 8, ucfirst($appointment->pet->gender ?? 'Not specified'), 0, 1);
                }
            } else {
                $pdf->SetX($leftX);
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->SetTextColor(100, 100, 100);
                $pdf->Cell(0, 8, 'No pet information available', 0, 1);
            }
            
            // Reason for visit
            $pdf->Ln(5);
            $pdf->SetX($leftX);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->Cell(0, 8, 'Reason for Visit', 0, 1, 'L');
            
            $pdf->SetX($leftX);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(60, 60, 60);
            $pdf->MultiCell(180, 6, $appointment->reason ?? 'No reason specified', 0, 'L');
            
            // Notes if available
            if (!empty($appointment->notes)) {
                $pdf->Ln(3);
                $pdf->SetX($leftX);
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->Cell(0, 8, 'Additional Notes', 0, 1, 'L');
                
                $pdf->SetX($leftX);
                $pdf->SetFont('Arial', 'I', 10);
                $pdf->SetTextColor(60, 60, 60);
                $pdf->MultiCell(180, 6, $appointment->notes, 0, 'L');
            }
            
            $pdf->Ln(20);
            $appointmentNumber++;
            
            // Add a page break if needed (not for the last appointment)
            if ($appointment != $appointments->last()) {
                // Check if we're close to the bottom of the page
                if ($pdf->GetY() > 200) {
                    $pdf->AddPage();
                }
            }
        }
        
        // Output the PDF
        return $pdf->Output('D', 'All_Appointments.pdf');
    }
} 