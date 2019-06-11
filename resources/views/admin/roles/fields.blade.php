<div class="row">
    <div class="form-group col-md-12">
        <div class="form-group">
            {!! Form::label('name', 'Name*', ['class' => 'control-label']) !!}
            {!! Form::text('name', old('name'), ['class' => 'form-control inpt-focus', 'placeholder' => '', 'required' => '']) !!}
            <p class="help-block"></p>
            @if($errors->has('name'))
                <p class="help-block">
                    {{ $errors->first('name') }}
                </p>
            @endif
        </div>
    </div>
</div>
<h4>General Permissions</h4>
<table class="table table-striped table-bordered table-hover order-column role_datatable">
    <thead>
    <tr>
        <th style="width: 171px;">Module</th>
        <th style="width: 100px;">Display</th>
        @foreach($permissionsMapping as $key => $name)
            <th style="width: 100px;">{{ $name }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @if(count($Permissions))
        @foreach($Permissions as $Permission)
            <tr>
                <th style="width: 171px;">{{ $Permission['title'] }}</th>
                <td style="width: 100px;">
                    <input id="allow_{{ $Permission['name'] }}" type="checkbox" name="permission[]"
                           class="allow_all allow {{ $Permission['name'] }} allow_{{ $Permission['name'] }}"
                           value="{{ $Permission['name'] }}"
                           @if(isset($AllowedPermissions[$Permission['id']])) checked="true"
                           @endif onclick="FormValidation.checkMyModule(this,'allow_{{ $Permission['name'] }}');">
                </td>
                @foreach($permissionsMapping as $key => $name)
                    <td style="width: 100px;">
                        @if(array_key_exists($Permission['key'] . $key, $Permission['children']))
                            <input id="sub-allow_{{ $Permission['children'][$Permission['key'] . $key]['name'] }}"
                                   type="checkbox" name="permission[]"
                                   class="allow_all allow {{ $Permission['name'] }}  sub-allow_{{ $Permission['name'] }}"
                                   value="{{ $Permission['children'][$Permission['key'] . $key]['name'] }}"
                                   @if(isset($AllowedPermissions[$Permission['children'][$Permission['key'] . $key]['id']])) checked="true"
                                   @endif onclick="FormValidation.checkMyParent(this,'allow_{{ $Permission['name'] }}' , 'sub-allow_{{ $Permission['name'] }}', '{{ $Permission['children'][$Permission['key'] . $key]['name'] }}' );">
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    @endif
    </tbody>
</table>
