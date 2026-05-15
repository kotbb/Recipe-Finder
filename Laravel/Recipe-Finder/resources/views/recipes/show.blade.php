@extends('layouts.master')

@section('content')
<main class="main-content">

  <section class="form-section" aria-label="View recipe">

    <div class="section-header" style="margin-bottom:28px">
      <div>
        <div class="section-eyebrow">Your Collection</div>
        <h2 class="section-title">{{ $recipe->name }}</h2>
      </div>
      <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">← Back</a>
    </div>

    <div class="form-card">

      {{-- Recipe Image --}}
      @if ($recipe->image_path)
        <img
          src="{{ Storage::url($recipe->image_path) }}"
          alt="{{ $recipe->name }}"
          style="width:100%;max-height:420px;object-fit:cover;border-radius:12px 12px 0 0;"
        />
      @else
        <div style="width:100%;height:220px;background:var(--surface-alt);display:flex;align-items:center;justify-content:center;font-size:4rem;border-radius:12px 12px 0 0;">
          🍽️
        </div>
      @endif

      <div class="form-card-body">

        {{-- Ingredients --}}
        <div class="form-group full-width" style="margin-bottom:28px">
          <label style="font-weight:600;font-size:1rem;margin-bottom:10px;display:block;">Ingredients</label>

        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
          @php
          $ings = $recipe->ingredients;
          if (is_string($ings)) $ings = json_decode($ings, true);
          if (is_string($ings)) $ings = json_decode($ings, true); // double-encodeds
          if (!is_array($ings)) $ings = [];
        @endphp
      @foreach ($ings as $item)
      <li style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:var(--surface-alt);border-radius:8px;">
        <span style="flex:1;">{{ $item['name'] ?? '' }}</span>
        @if (!empty($item['grams']) && $item['grams'] !== '0')
          <span style="color:var(--ink-muted);font-size:.85rem;">{{ $item['grams'] }}g</span>
        @endif
      </li>
    @endforeach
    </ul>
        </div>

        {{-- Instructions --}}
        <div class="form-group full-width" style="margin-bottom:28px">
          <label style="font-weight:600;font-size:1rem;margin-bottom:10px;display:block;">Instructions</label>
          <div style="line-height:1.8;color:var(--ink-body);white-space:pre-line;">{{ $recipe->instructions }}</div>
        </div>

        {{-- Actions --}}
        <div class="form-actions">
          <a href="{{ route('recipes.edit', $recipe->id) }}" class="btn btn-primary">Edit Recipe</a>
          <form action="{{ route('recipes.destroy', $recipe->id) }}" method="POST"
                onsubmit="return confirm('Delete this recipe?')" style="display:inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
          <a href="{{ route('home') }}" class="btn btn-secondary">Back to My Recipes</a>
        </div>

      </div>
    </div>

  </section>

</main>
@endsection
