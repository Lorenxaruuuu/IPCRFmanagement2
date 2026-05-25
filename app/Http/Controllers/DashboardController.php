<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Services\GeminiService;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $metrics = [
            ['category' => 'Community Outreach', 'score' => 85, 'description' => 'Total households reached this quarter'],
            ['category' => 'Resource Allocation', 'score' => 92, 'description' => 'Budget efficiency vs target'],
            ['category' => 'Response Time', 'score' => 74, 'description' => 'Average response to emergency requests']
        ];

        // Fetch AI insights
        $insights = $this->gemini->getPerformanceInsights($metrics);
        
        // Mock user profile data as seen in the request image
        $user = Session::get('user');
        $profile = [
            'full_name' => 'John Doe',
            'email' => 'alexarawles@gmail.com',
            'birthday' => 'January 1, 2002',
            'gender' => 'Female',
            'address' => 'Sample City',
            'region' => 'sample',
            'secondary_emails' => [
                'alexarawles@gmail.com'
            ]
        ];

        return view('dashboard', compact('metrics', 'insights', 'user', 'profile'));
    }
}
