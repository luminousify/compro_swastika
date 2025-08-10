<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ContactMessageController extends Controller
{
    /**
     * Display a listing of contact messages
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query();
        
        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('created_by_ip', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'handled') {
                $query->where('handled', true);
            } elseif ($request->status === 'unhandled') {
                $query->where('handled', false);
            }
        }
        
        // Order: unhandled first, then by created_at desc
        $query->orderBy('handled', 'asc')
              ->orderBy('created_at', 'desc');
        
        $messages = $query->paginate(20)->withQueryString();
        
        return view('admin.messages.index', compact('messages'));
    }
    
    /**
     * Display the specified message
     */
    public function show(ContactMessage $message)
    {
        return view('admin.messages.show', compact('message'));
    }
    
    /**
     * Update the handled status of a message
     */
    public function handle(Request $request, ContactMessage $message)
    {
        $validated = $request->validate([
            'handled' => 'required|boolean',
            'note' => 'nullable|string|max:1000',
        ]);
        
        $message->update([
            'handled' => $validated['handled'],
            'note' => $validated['note'] ?? $message->note,
        ]);
        
        return redirect()->route('admin.messages.index')
            ->with('success', $validated['handled'] 
                ? 'Message marked as handled' 
                : 'Message marked as unhandled');
    }
    
    /**
     * Delete a single message
     */
    public function destroy(ContactMessage $message)
    {
        $message->delete();
        
        return redirect()->route('admin.messages.index')
            ->with('success', 'Message deleted successfully');
    }
    
    /**
     * Export messages to CSV
     */
    public function export(Request $request)
    {
        $query = ContactMessage::query();
        
        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            if ($request->status === 'handled') {
                $query->where('handled', true);
            } elseif ($request->status === 'unhandled') {
                $query->where('handled', false);
            }
        }
        
        $messages = $query->orderBy('created_at', 'desc')->get();
        
        // Create CSV content with UTF-8 BOM
        $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csv .= "Name,Email,Phone,Company,Message,Status,Submitted At,IP Address,Internal Notes\n";
        
        foreach ($messages as $message) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                str_replace('"', '""', $message->name),
                str_replace('"', '""', $message->email ?? ''),
                str_replace('"', '""', $message->phone ?? ''),
                str_replace('"', '""', $message->company ?? ''),
                str_replace('"', '""', $message->message),
                $message->handled ? 'Handled' : 'Unhandled',
                $message->created_at->format('Y-m-d H:i:s'),
                $message->created_by_ip,
                str_replace('"', '""', $message->note ?? '')
            );
        }
        
        $filename = 'contact-messages-' . date('Y-m-d-His') . '.csv';
        
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
    
    /**
     * Purge messages older than 24 months
     */
    public function purge()
    {
        $cutoffDate = Carbon::now()->subMonths(24);
        
        $count = ContactMessage::where('created_at', '<', $cutoffDate)->count();
        
        ContactMessage::where('created_at', '<', $cutoffDate)->delete();
        
        return redirect()->route('admin.messages.index')
            ->with('success', "Purged {$count} messages older than 24 months");
    }
}