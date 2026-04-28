<?php

namespace App\Livewire\Admin;

use App\Models\FormSubmission;
use Livewire\Component;
use Livewire\WithPagination;

class FormSubmissionsTable extends Component
{
    use WithPagination;

    public string $search   = '';
    public string $type     = '';
    public string $status   = '';
    public array  $selected = [];
    public bool   $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'type'   => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingType(): void    { $this->resetPage(); }
    public function updatingStatus(): void  { $this->resetPage(); }

    public function updatedSelectAll(bool $value): void
    {
        $this->selected = $value
            ? $this->getQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];
    }

    public function delete(int $id): void
    {
        try {
            FormSubmission::findOrFail($id)->delete();
        } catch (\Throwable) {
            \DB::table('form_submissions')->where('id', $id)->delete();
        }
        $this->selected = array_filter($this->selected, fn($s) => (int)$s !== $id);
        session()->flash('success', 'Lead eliminado');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) return;
        \DB::table('form_submissions')->whereIn('id', $this->selected)->delete();
        $count = count($this->selected);
        $this->selected  = [];
        $this->selectAll = false;
        session()->flash('success', "{$count} leads eliminados");
    }

    private function getQuery()
    {
        return FormSubmission::query()
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('full_name', 'like', "%{$this->search}%")
                   ->orWhere('email',    'like', "%{$this->search}%")
                   ->orWhere('phone',    'like', "%{$this->search}%")
            ))
            ->when($this->type,   fn($q) => $q->where('form_type', $this->type))
            ->when($this->status, fn($q) => $q->where('status',    $this->status))
            ->latest();
    }

    public function render()
    {
        $submissions = $this->getQuery()->paginate(25);

        $counts = [
            'total'     => FormSubmission::count(),
            'new'       => FormSubmission::where('status', 'new')->count(),
            'vendedor'  => FormSubmission::where('form_type', 'vendedor')->count(),
            'comprador' => FormSubmission::where('form_type', 'comprador')->count(),
            'b2b'       => FormSubmission::where('form_type', 'b2b')->count(),
            'contacto'  => FormSubmission::where('form_type', 'contacto')->count(),
        ];

        return view('livewire.admin.form-submissions-table', [
            'submissions' => $submissions,
            'counts'      => $counts,
        ]);
    }
}
