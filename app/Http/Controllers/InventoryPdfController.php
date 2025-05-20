<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Services\TenantDatabaseService;
use Illuminate\Http\Request;
require_once base_path('vendor/setasign/fpdf/fpdf.php');

// Extend FPDF to create custom header and footer
class InventoryPDF extends \FPDF
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
            // No logo fallback - just use text
            $logoOffset = 10;
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
}

class InventoryPdfController extends Controller
{
    protected $tenantDatabaseService;

    public function __construct(TenantDatabaseService $tenantDatabaseService)
    {
        $this->tenantDatabaseService = $tenantDatabaseService;
    }

    private function getClinic(Request $request)
    {
        // Try getting clinic from subdomain first
        $host = $request->getHost();
        $appDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? '';
        $subdomain = null;

        if ($host !== $appDomain && str_contains($host, $appDomain)) {
            $subdomain = str_replace('.' . $appDomain, '', $host);
        }

        if ($subdomain) {
            $clinic = \App\Models\Clinic::where('subdomain', $subdomain)->first();
            if ($clinic) {
                return $clinic;
            }
        }
        
        // Fall back to session-based clinic
        $clinicId = session('current_clinic_id');
        if ($clinicId) {
            return \App\Models\Clinic::find($clinicId);
        }

        return null;
    }

    public function generateInventoryPdf(Request $request)
    {
        try {
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

            // Get all inventory items
            $inventoryItems = Inventory::orderBy('name')->get();
            
            // Create PDF with clinic data
            $pdf = new InventoryPDF($clinic);
            $pdf->AliasNbPages(); // For page numbering
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true, 30); // 30mm bottom margin
            
            // Set default colors - Softer, less saturated colors
            $primaryColor = [70, 130, 180]; // Softer blue (steel blue)
            $secondaryColor = [128, 137, 145]; // Softer secondary
            $lightBackground = [248, 250, 252]; // Light background
            $successColor = [76, 175, 80]; // Softer green
            
            // Add inventory title with styled header
            $pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->Rect(0, 45, 210, 15, 'F');
            $pdf->SetFont('Arial', 'B', 18);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetY(48);
            $pdf->Cell(0, 8, 'Inventory List', 0, 1, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(70, 70, 70);
            $pdf->SetY(58);
            $pdf->Cell(0, 5, 'Generated on ' . now()->format('F d, Y'), 0, 1, 'C');
            $pdf->Ln(8);
            
            // Remove paw decoration
            // $pdf->DrawPaw(180, 60, 8);
            
            // Summary section with styled box
            $pdf->SetFillColor($lightBackground[0], $lightBackground[1], $lightBackground[2]);
            $pdf->RoundedRect(10, $pdf->GetY(), 190, 25, 4, 'F');
            
            // Summary section header
            $pdf->SetY($pdf->GetY() + 3);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->Cell(0, 8, '  Summary', 0, 1, 'L');
            
            // Add inventory count summary
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->Cell(60, 8, '  Total Items: ' . $inventoryItems->count(), 0, 1);
            
            // Group items by category
            $categoryCounts = $inventoryItems->groupBy('category')->map->count();
            $totalInStock = $inventoryItems->sum('stock_quantity');
            $lowStockCount = $inventoryItems->where('stock_quantity', '<', 10)->count();
            
            // Add additional summary info
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetX(15);
            $pdf->Cell(70, 6, 'Total Items in Stock: ' . $totalInStock, 0, 0);
            $pdf->Cell(70, 6, 'Low Stock Items: ' . $lowStockCount, 0, 1);
            
            $pdf->Ln(10);
            
            // Category breakdown section
            if ($categoryCounts->count() > 0) {
                $pdf->SetFillColor($lightBackground[0], $lightBackground[1], $lightBackground[2]);
                $pdf->RoundedRect(10, $pdf->GetY(), 190, min(10 + $categoryCounts->count() * 7, 60), 4, 'F');
                
                $pdf->SetY($pdf->GetY() + 3);
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                $pdf->Cell(0, 8, '  Categories', 0, 1, 'L');
                
                // List categories without color indicators
                $pdf->SetFont('Arial', '', 10);
                $pdf->SetTextColor(50, 50, 50);
                
                $startY = $pdf->GetY();
                $colWidth = 90;
                $rowHeight = 7;
                $col = 0;
                $row = 0;
                
                foreach ($categoryCounts as $category => $count) {
                    $xPos = 15 + ($col * $colWidth);
                    $yPos = $startY + ($row * $rowHeight);
                    
                    $pdf->SetY($yPos);
                    $pdf->SetX($xPos);
                    $pdf->Cell($colWidth, 6, ($category ?: 'Uncategorized') . ' (' . $count . ')', 0, 0);
                    
                    $col++;
                    if ($col >= 2) {
                        $col = 0;
                        $row++;
                    }
                }
                
                // Set Y position after categories
                $rowsNeeded = ceil(count($categoryCounts) / 2);
                $pdf->SetY($startY + $rowsNeeded * $rowHeight + 5);
            }
            
            $pdf->Ln(5);
            
            // Inventory table header
            $pdf->SetDrawColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetLineWidth(0.5);
            $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->Cell(0, 12, 'Inventory Items', 0, 1, 'C');
            
            // Table header
            $pdf->SetFillColor(240, 248, 255);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
            $pdf->SetX(10);
            $pdf->Cell(60, 10, 'Item Name', 1, 0, 'C', true);
            $pdf->Cell(40, 10, 'Category', 1, 0, 'C', true);
            $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
            $pdf->Cell(30, 10, 'Price', 1, 0, 'C', true);
            $pdf->Cell(30, 10, 'SKU', 1, 1, 'C', true);
            
            // Table content
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(70, 70, 70);
            
            $alternate = false;
            $currentCategory = null;
            
            // First, sort by category then by name
            $inventoryItems = $inventoryItems->sortBy([
                ['category', 'asc'],
                ['name', 'asc']
            ]);
            
            foreach ($inventoryItems as $item) {
                // Check for category change
                if ($currentCategory !== $item->category) {
                    $currentCategory = $item->category;
                    $alternate = false;
                    
                    // If not first category, add some space
                    if ($item !== $inventoryItems->first()) {
                        $pdf->Ln(3);
                    }
                }
                
                // Alternate row background
                if ($alternate) {
                    $pdf->SetFillColor(245, 250, 255);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }
                $alternate = !$alternate;
                
                // Highlight low stock items
                if ($item->stock_quantity < 10) {
                    $pdf->SetTextColor(200, 0, 0); // Red text for low stock
                } else {
                    $pdf->SetTextColor(70, 70, 70); // Normal text color
                }
                
                $pdf->SetX(10);
                $pdf->Cell(60, 10, $item->name, 1, 0, 'L', true);
                $pdf->Cell(40, 10, $item->category ?: 'N/A', 1, 0, 'C', true);
                $pdf->Cell(30, 10, $item->stock_quantity, 1, 0, 'C', true);
                $pdf->Cell(30, 10, 'PHP ' . number_format($item->selling_price, 2), 1, 0, 'R', true);
                $pdf->Cell(30, 10, $item->sku ?: 'N/A', 1, 1, 'C', true);
                
                // Check if we need a page break
                if ($pdf->GetY() > 250 && $item !== $inventoryItems->last()) {
                    $pdf->AddPage();
                    
                    // Repeat table header on new page
                    $pdf->SetFillColor(240, 248, 255);
                    $pdf->SetFont('Arial', 'B', 11);
                    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
                    $pdf->SetX(10);
                    $pdf->Cell(60, 10, 'Item Name', 1, 0, 'C', true);
                    $pdf->Cell(40, 10, 'Category', 1, 0, 'C', true);
                    $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
                    $pdf->Cell(30, 10, 'Price', 1, 0, 'C', true);
                    $pdf->Cell(30, 10, 'SKU', 1, 1, 'C', true);
                    
                    $pdf->SetFont('Arial', '', 10);
                    $alternate = false;
                }
            }
            
            // First save the file to disk
            $outputPath = public_path('tmp/Inventory_List.pdf');
            $pdf->Output('F', $outputPath);
            
            // Then return it as a download response
            return response()->download($outputPath, 'Inventory_List.pdf', [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('PDF Generation Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('inventory.index')
                ->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }
} 