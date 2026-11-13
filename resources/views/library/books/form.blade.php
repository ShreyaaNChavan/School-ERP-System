@extends('layouts.schoolnexa')

@section('content')
<div class="sn-page">
    @include('library._styles')

    @if(session('success'))
        <div class="sn-alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="sn-alert sn-error">
            <ul style="margin:0;padding-left:18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PAGE HEADER --}}
    <div class="sn-head">
        <div>
            <h1 class="sn-title">
                {{ $book->exists ? 'Edit Book' : 'Add Book' }}
            </h1>
            <div class="sn-sub">
                Enter bibliographic details
            </div>
        </div>

        <div class="sn-actions">
            <a class="sn-btn sn-btn-outline"
               href="{{ route('library.books.index') }}">
                ← Back to Catalogue
            </a>
        </div>
    </div>

    {{-- BOOK FORM --}}
    <div class="sn-card">
        <form method="POST"
              action="{{ $book->exists
                  ? route('library.books.update', $book)
                  : route('library.books.store') }}">

            @csrf

            @if($book->exists)
                @method('PUT')
            @endif

            <h3 class="sn-section-title">Book Information</h3>

            <div class="sn-form">
                @foreach([
                    'title' => 'Title',
                    'isbn' => 'ISBN',
                    'author' => 'Author',
                    'publisher' => 'Publisher',
                    'category' => 'Category',
                    'edition' => 'Edition',
                    'publication_year' => 'Publication Year'
                ] as $field => $label)

                    <div class="sn-field">
                        <label>
                            {{ $label }}
                            @if($field === 'title')
                                <span style="color:#DC2626">*</span>
                            @endif
                        </label>

                        <input
                            type="text"
                            name="{{ $field }}"
                            value="{{ old($field, $book->$field) }}"
                            @required($field === 'title')
                        >

                        @error($field)
                            <small style="color:#DC2626">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                @endforeach
            </div>

            <div class="sn-actions"
                 style="margin-top:22px;padding-top:18px;border-top:1px solid #E2E8F0">

                <button type="submit" class="sn-btn sn-btn-primary">
                    {{ $book->exists ? 'Update Book' : 'Save Book' }}
                </button>

                <a class="sn-btn sn-btn-outline"
                   href="{{ route('library.books.index') }}">
                    Cancel
                </a>
            </div>

        </form>
    </div>
</div>
@endsection