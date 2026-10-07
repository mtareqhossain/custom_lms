<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('course')->where('user_id', Auth::id())->where('status', 'paid')->latest('paid_at')->get();

        return view('orders.index', compact('orders'));
    }
}
