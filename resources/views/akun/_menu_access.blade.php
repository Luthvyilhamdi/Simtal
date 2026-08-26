{{--
    Panel centang akses menu — dipakai bersama oleh modal Tambah & Edit.
    Butuh dari scope pemanggil: $menuGroups, $totalMenu
    Parameter @include: $prefix ('create'|'edit'), $selected (array key menu)
--}}
<div class="form-group full" id="{{ $prefix }}MenuPanel" style="display:none;">
    <div class="ma-head">
        <label class="form-label" style="margin:0;">Akses Menu</label>
        <div class="ma-head-right">
            <span class="ma-count" id="{{ $prefix }}MenuCount">0 / {{ $totalMenu }}</span>
            <div class="menu-access-tools">
                <button type="button" onclick="toggleAllMenus('{{ $prefix }}MenuBox')">Pilih semua</button>
                <button type="button" onclick="toggleAllMenus('{{ $prefix }}MenuBox', false)">Kosongkan</button>
            </div>
        </div>
    </div>
    <div class="menu-access-box" id="{{ $prefix }}MenuBox">
        @foreach($menuGroups as $group => $items)
        <div class="ma-group">
            <div class="ma-group-label">{{ $group }}</div>
            <div class="ma-items">
                @foreach($items as $key => $def)
                <label class="ma-check">
                    <input type="checkbox" name="menu_access[]" value="{{ $key }}"
                           {{ in_array($key, $selected, true) ? 'checked' : '' }}>
                    <span>{{ $def['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    <span class="form-hint">Centang menu yang boleh dibuka admin ini.</span>
</div>
