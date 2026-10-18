@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'hint' => null, 'options' => null, 'rows' => 4, 'placeholder' => null, 'accept' => null, 'multiple' => false])
@php
    $id = 'f-'.str_replace(['[', ']', '.'], '-', $name);
    // Dot notation (stats.3.label) for old()/errors; bracket notation (stats[3][label]) for the HTML name.
    $htmlName = preg_replace('/\.(\w+)/', '[$1]', $name);
    $current = old($name, $value);
    $err = $errors->has($name);
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }}@if ($required)<span class="text-brand" aria-hidden="true"> *</span>@endif</label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $htmlName }}" rows="{{ $rows }}" @required($required) placeholder="{{ $placeholder }}"
            @class(['input', 'is-invalid' => $err]) @if ($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif>{{ $current }}</textarea>
    @elseif ($type === 'select')
        <select id="{{ $id }}" name="{{ $htmlName }}" @required($required) @class(['input', 'is-invalid' => $err]) @if ($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif>
            <option value="">{{ $placeholder ?? 'Select…' }}</option>
            @foreach ($options as $key => $text)
                @php $optValue = is_int($key) ? $text : $key; @endphp
                <option value="{{ $optValue }}" @selected((string) $current === (string) $optValue)>{{ $text }}</option>
            @endforeach
        </select>
    @elseif ($type === 'file')
        <input id="{{ $id }}" type="file" name="{{ $htmlName }}{{ $multiple ? '[]' : '' }}" @if ($multiple) multiple @endif @if ($accept) accept="{{ $accept }}" @endif @required($required)
            class="input file:mr-4 file:rounded-lg file:border-0 file:bg-navy file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-navy-700" @if ($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $htmlName }}" value="{{ $type === 'password' ? '' : $current }}" @required($required) placeholder="{{ $placeholder }}"
            @class(['input', 'is-invalid' => $err]) @if ($err) aria-invalid="true" aria-describedby="{{ $id }}-err" @endif>
    @endif

    @if ($hint)<p class="hint">{{ $hint }}</p>@endif
    @error($name)<p id="{{ $id }}-err" class="field-error">{{ $message }}</p>@enderror
</div>
