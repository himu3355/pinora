<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\Faq;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        return view('pages.show', compact('page'));
    }

    public function about()
    {
        $page = Page::published()->where('slug', 'about-us')->first();

        if ($page) {
            return view('pages.show', compact('page'));
        }

        // Fallback default
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:2000',
        ]);

        ContactInquiry::create($validated);

        return back()->with('success', 'Thank you for reaching out! Your message has been received. Our Rajkot support team will get back to you shortly.');
    }

    public function faq()
    {
        $faqs = Faq::active()->get()->groupBy('category');

        return view('pages.faq', compact('faqs'));
    }
}
