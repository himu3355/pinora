@extends('layouts.app')

@section('title', 'Manage Addresses')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <div class="w-full md:w-64 shrink-0">
            @include('account.partials.sidebar', ['active' => 'addresses'])
        </div>

        <div class="flex-grow bg-dark-card border border-border-gold rounded-2xl shadow-sm p-6 md:p-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-text-light tracking-tight">Saved Addresses</h1>
                    <p class="text-text-muted mt-1">Manage shipping locations for convenient one-click checkouts.</p>
                </div>
                <div>
                    <button onclick="toggleForm('create')" class="btn btn-gold">
                        Add New Address
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-400 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Address Form (Initially Hidden) -->
            <div id="address-form-container" class="hidden mb-8 p-6 bg-dark-bg border border-border-gold rounded-2xl">
                <h3 id="form-title" class="text-lg font-bold text-text-light mb-6">Add a New Address</h3>
                
                <form id="address-form" method="POST" action="{{ route('account.addresses.store') }}">
                    @csrf
                    <input type="hidden" id="form-method" name="_method" value="POST">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label for="full_name" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Recipient Name *</label>
                            <input type="text" name="full_name" id="full_name" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="phone" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Phone Number *</label>
                            <input type="text" name="phone" id="phone" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                        <div>
                            <label for="type" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Address Type *</label>
                            <select name="type" id="type" required class="w-full bg-dark-bg border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition cursor-pointer">
                                <option value="home">Home</option>
                                <option value="work">Work / Office</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="label" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Optional Label (e.g. Mom's House)</label>
                            <input type="text" name="label" id="label" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>

                    <div class="mb-5">
                        <label for="address_line_1" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Street Address *</label>
                        <input type="text" name="address_line_1" id="address_line_1" required placeholder="House No, Apartment, Street" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition placeholder:text-text-muted">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label for="address_line_2" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Address Line 2</label>
                            <input type="text" name="address_line_2" id="address_line_2" placeholder="Suite, Unit, Area (Optional)" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition placeholder:text-text-muted">
                        </div>
                        <div>
                            <label for="landmark" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Landmark</label>
                            <input type="text" name="landmark" id="landmark" placeholder="e.g. Near City Mall (Optional)" class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition placeholder:text-text-muted">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
                        <div>
                            <label for="city" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">City *</label>
                            <input type="text" name="city" id="city" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                        <div>
                            <label for="state" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">State / UT *</label>
                            <select name="state" id="state" required class="w-full bg-dark-bg border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition cursor-pointer">
                                <option value="">Select State</option>
                                @foreach($states as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="pincode" class="block text-xs font-bold text-text-muted uppercase tracking-wide mb-2">Pincode *</label>
                            <input type="text" name="pincode" id="pincode" required class="w-full bg-white/5 border border-border-gold rounded-xl px-4 py-3 text-sm text-text-light focus:border-gold outline-none transition">
                        </div>
                    </div>

                    <div class="flex items-center mb-6">
                        <input type="checkbox" name="is_default" id="is_default" value="1" class="h-4.5 w-4.5 text-gold focus:ring-gold border-border-gold bg-transparent rounded accent-gold">
                        <label for="is_default" class="ml-2.5 text-sm font-semibold text-text-muted">Set as default shipping address</label>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="toggleForm('hide')" class="px-5 py-2.5 border border-border-gold text-text-muted font-bold text-sm rounded-xl hover:bg-gold/5 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-gold hover:bg-gold-light text-dark-bg font-bold text-sm rounded-xl shadow-sm transition">
                            Save Address
                        </button>
                    </div>
                </form>
            </div>

            <!-- Addresses List -->
            @if($addresses->isEmpty())
                <div class="text-center py-16 border border-dashed border-border-gold/55 rounded-2xl">
                    <p class="text-text-muted text-base">You haven't saved any addresses yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($addresses as $address)
                        <div class="border border-border-gold/55 p-6 rounded-2xl bg-dark-card shadow-sm relative flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <span class="px-2.5 py-1 bg-gold/10 border border-border-gold/50 text-gold text-xs font-bold rounded-full uppercase tracking-wider">
                                        {{ $address->label ?? ucfirst($address->type) }}
                                    </span>
                                    @if($address->is_default)
                                        <span class="bg-gold/10 text-gold text-xs font-bold px-2.5 py-1 rounded-full uppercase">
                                            Default
                                        </span>
                                    @endif
                                </div>
                                <h3 class="font-extrabold text-text-light text-lg mb-1">{{ $address->full_name }}</h3>
                                <p class="text-sm text-text-muted leading-relaxed">{{ $address->address_line_1 }}</p>
                                @if($address->address_line_2)
                                    <p class="text-sm text-text-muted leading-relaxed">{{ $address->address_line_2 }}</p>
                                @endif
                                @if($address->landmark)
                                    <p class="text-sm text-text-muted/80 leading-relaxed italic mt-0.5">Near: {{ $address->landmark }}</p>
                                @endif
                                <p class="text-sm text-text-muted leading-relaxed mt-0.5">{{ $address->city }}, {{ $address->state }} - {{ $address->pincode }}</p>
                                <p class="text-sm text-text-muted mt-3 font-medium"><span class="text-text-muted/65">Phone:</span> {{ $address->phone }}</p>
                            </div>

                            <div class="border-t border-border-gold/30 pt-4 mt-6 flex justify-between items-center">
                                <div class="flex gap-3">
                                    <button onclick="editAddress({{ json_encode($address) }})" class="text-xs font-bold text-text-muted hover:text-gold transition">
                                        Edit
                                    </button>
                                    
                                    <form action="{{ route('account.addresses.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-750 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>

                                @if(!$address->is_default)
                                    <form action="{{ route('account.addresses.default', $address->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold text-gold hover:text-gold-light transition">
                                            Set as Default
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

<script>
    function toggleForm(action) {
        const container = document.getElementById('address-form-container');
        if (action === 'create') {
            document.getElementById('form-title').innerText = 'Add a New Address';
            document.getElementById('address-form').action = "{{ route('account.addresses.store') }}";
            document.getElementById('form-method').value = "POST";
            document.getElementById('address-form').reset();
            container.classList.remove('hidden');
            container.scrollIntoView({ behavior: 'smooth' });
        } else if (action === 'hide') {
            container.classList.add('hidden');
        }
    }

    function editAddress(address) {
        const container = document.getElementById('address-form-container');
        document.getElementById('form-title').innerText = 'Edit Address';
        
        let actionUrl = "{{ route('account.addresses.update', ':id') }}";
        actionUrl = actionUrl.replace(':id', address.id);
        
        document.getElementById('address-form').action = actionUrl;
        document.getElementById('form-method').value = "PUT";
        
        // Resolve type and label based on DB label value
        let typeVal = 'other';
        let labelVal = address.label || '';
        
        const lowerLabel = labelVal.toLowerCase().trim();
        if (lowerLabel === 'home') {
            typeVal = 'home';
            labelVal = '';
        } else if (lowerLabel === 'work' || lowerLabel === 'office') {
            typeVal = 'work';
            labelVal = '';
        }
        
        // Populate inputs
        document.getElementById('full_name').value = address.full_name;
        document.getElementById('phone').value = address.phone;
        document.getElementById('type').value = typeVal;
        document.getElementById('label').value = labelVal;
        document.getElementById('address_line_1').value = address.address_line_1;
        document.getElementById('address_line_2').value = address.address_line_2 || '';
        document.getElementById('landmark').value = address.landmark || '';
        document.getElementById('city').value = address.city;
        document.getElementById('state').value = address.state;
        document.getElementById('pincode').value = address.pincode;
        document.getElementById('is_default').checked = address.is_default;
        
        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth' });
    }
</script>
@endsection
