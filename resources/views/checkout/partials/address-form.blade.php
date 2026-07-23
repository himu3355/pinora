<div class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Full Name *</label>
        <input type="text" name="new_address[full_name]" value="{{ old('new_address.full_name') }}" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.full_name')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Phone *</label>
        <input type="tel" name="new_address[phone]" value="{{ old('new_address.phone') }}" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.phone')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Pincode *</label>
        <input type="text" name="new_address[pincode]" value="{{ old('new_address.pincode') }}" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.pincode')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-span-2">
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Address Line 1 *</label>
        <input type="text" name="new_address[address_line_1]" value="{{ old('new_address.address_line_1') }}" placeholder="Flat, House No., Building, Street" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.address_line_1')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div class="col-span-2">
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">Address Line 2</label>
        <input type="text" name="new_address[address_line_2]" value="{{ old('new_address.address_line_2') }}" placeholder="Area, Colony, Landmark (optional)" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
    </div>
    <div>
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">City *</label>
        <input type="text" name="new_address[city]" value="{{ old('new_address.city') }}" class="w-full bg-white/5 border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm outline-none focus:border-gold transition-colors">
        @error('new_address.city')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
    <div>
        <label class="text-[0.8rem] text-text-muted block mb-1.5 font-medium">State *</label>
        <select name="new_address[state]" class="w-full bg-dark-bg border border-border-gold rounded-lg py-2.5 px-4 text-text-light font-secondary text-sm cursor-pointer outline-none focus:border-gold transition-colors">
            <option value="">Select State</option>
            @foreach(\App\Models\Address::INDIAN_STATES as $state)
            <option value="{{ $state }}" {{ old('new_address.state') === $state ? 'selected' : '' }}>{{ $state }}</option>
            @endforeach
        </select>
        @error('new_address.state')<div class="text-[#f08080] text-[0.78rem] mt-1">{{ $message }}</div>@enderror
    </div>
</div>
