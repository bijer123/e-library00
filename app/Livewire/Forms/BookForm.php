<?php

namespace App\Livewire\Forms;

use App\Models\Book;
use Illuminate\Validation\Rule;
use Livewire\Form;

class BookForm extends Form
{
    public string $title = '';
    public string $isbn = '';
    public string $description = '';
    public int $stock = 0;
    public ?int $category_id = null;

    public ?Book $book = null;

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'isbn' => [
                'nullable',
                'string',
                Rule::unique('books', 'isbn')->ignore($this->book?->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'], // ini bagian khusus relasi
        ];
    }

    public function setBook(Book $book): void
    {
        $this->book = $book;
        $this->title = $book->title;
        $this->isbn = $book->isbn ?? '';
        $this->description = $book->description ?? '';
        $this->stock = $book->stock;
        $this->category_id = $book->category_id;
    }

    public function store()
    {
        $this->validate();
        Book::create($this->only(['title', 'isbn', 'description', 'stock', 'category_id']));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->book->update($this->only(['title', 'isbn', 'description', 'stock', 'category_id']));
    }
}