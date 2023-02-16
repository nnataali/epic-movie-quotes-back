<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuoteRequest;
use App\Http\Requests\Admin\UpdateQuoteRequest;
use App\Models\Quote;
use Illuminate\Support\Facades\Storage;

class QuoteController extends Controller
{
	public function index($id)
	{
		$quotes = Quote::where('movie_id', $id)
						->with('movie')
						->orderBy('created_at', 'desc')
						->get()
						->map(function ($quote) {
							$quote->quote = json_decode($quote->quote);
							$quote->image = Storage::url($quote->image);
							return $quote;
						});

		return response()->json($quotes);
	}

	public function store(StoreQuoteRequest $request)
	{
		$quote = Quote::create([
			'quote'          => json_encode([
				'en' => $request['quote_en'],
				'ge' => $request['quote_ge'],
			]),
			'movie_id'       => $request->movie_id,
			'user_id'        => auth()->user()->id,
			'image'          => request()->file('image')->store('images'),
		]);

		if (!$quote)
		{
			return response()->json('Error has occurred', 422);
		}

		return response()->json('success', 201);
	}

	public function get($id)
	{
		$quote = Quote::where('id', $id)
			->with('user')
			->with('movie')
			->get()
			->map(function ($quote) {
				$quote->quote = json_decode($quote->quote);
				$quote->image = Storage::url($quote->image);
				return $quote;
			});
		return response()->json($quote, 200);
	}

	public function update(UpdateQuoteRequest $request)
	{
		$quote = Quote::where('id', $request->id)->first();

		$attributes = [
			'quote' => [
				'en' => $request['quote_en'],
				'ge' => $request['quote_ge'],
			],
		];

		if (isset($request['image']))
		{
			$attributes['image'] = request()->file('image')->store('images');
		}

		$quote->update($attributes);
		return response()->json($request, 200);
	}

	public function destroy($id)
	{
		$quote = Quote::where('id', $id);
		$quote->delete();
		return response()->json('Quote deleted successfully', 200);
	}
}
