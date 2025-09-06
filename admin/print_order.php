<?php
session_start();
include_once __DIR__ . '/../includes/config.php';

// Check if the admin is logged in, otherwise redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Path to the sales JSON file
$salesFile = __DIR__ . '/../sales.json';

// Get order ID from URL
$order_id = $_GET['id'] ?? '';

if (empty($order_id)) {
    header('Location: sales.php');
    exit;
}

// Load sales data
$sales = [];
if (file_exists($salesFile)) {
    $json_content = file_get_contents($salesFile);
    $sales = json_decode($json_content, true);
    if (!is_array($sales)) {
        $sales = [];
    }
}

// Find the specific order
$order = null;
foreach ($sales as $sale) {
    if ($sale['order_id'] === $order_id) {
        $order = $sale;
        break;
    }
}

if (!$order) {
    header('Location: sales.php');
    exit;
}

// Set content type for printing
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo htmlspecialchars($order['order_id']); ?> - <?php echo SITE_NAME; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1dd171;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #1dd171;
            margin-bottom: 10px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 16px;
            color: #666;
        }
        .invoice-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .detail-section h3 {
            margin: 0 0 10px 0;
            color: #1dd171;
            font-size: 16px;
        }
        .detail-section p {
            margin: 5px 0;
            font-size: 14px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #1dd171;
        }
        .items-table .text-right {
            text-align: right;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            padding: 5px 0;
        }
        .total-line.final {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #1dd171;
            padding-top: 10px;
            margin-top: 15px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1dd171;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background: #16a34a;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .invoice-container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Print Invoice</button>
    
    <div class="invoice-container">
        <div class="header">
            <div class="logo"><?php echo SITE_NAME; ?></div>
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">Invoice #<?php echo htmlspecialchars($order['order_id']); ?></div>
        </div>

        <div class="invoice-details">
            <div class="detail-section">
                <h3>Bill To:</h3>
                <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
                <p><?php echo htmlspecialchars($order['customer_email']); ?></p>
                <?php if (isset($order['billing_address'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($order['billing_address'])); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="detail-section">
                <h3>Invoice Details:</h3>
                <p><strong>Invoice Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
                <p><strong>Invoice #:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                <?php if (isset($order['payment_method'])): ?>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                            <?php if (isset($item['product_slug'])): ?>
                                <br><small>SKU: <?php echo htmlspecialchars($item['product_slug']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?php echo htmlspecialchars($item['qty'] ?? $item['quantity']); ?></td>
                        <td class="text-right">$<?php echo number_format((float)$item['price'], 2); ?></td>
                        <td class="text-right">$<?php echo number_format((float)$item['price'] * (int)($item['qty'] ?? $item['quantity']), 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-line">
                <span>Subtotal:</span>
                <span>$<?php echo number_format((float)$order['total_amount'], 2); ?></span>
            </div>
            <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
            <div class="total-line">
                <span>Tax:</span>
                <span>$<?php echo number_format((float)$order['tax_amount'], 2); ?></span>
            </div>
            <?php endif; ?>
            <?php if (isset($order['shipping_amount']) && $order['shipping_amount'] > 0): ?>
            <div class="total-line">
                <span>Shipping:</span>
                <span>$<?php echo number_format((float)$order['shipping_amount'], 2); ?></span>
            </div>
            <?php endif; ?>
            <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
            <div class="total-line">
                <span>Discount:</span>
                <span>-$<?php echo number_format((float)$order['discount_amount'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="total-line final">
                <span>Total:</span>
                <span>$<?php echo number_format((float)$order['total_amount'], 2); ?></span>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For questions about this invoice, please contact us.</p>
            <p>Generated on <?php echo date('F j, Y \a\t g:i A'); ?></p>
        </div>
    </div>

    <script>
        // Auto-print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
