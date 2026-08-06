@include('partials.master-data.field', ['name' => $codeField, 'label' => $codeLabel, 'value' => $model->{$codeField}, 'required' => true])
@include('partials.master-data.field', ['name' => $nameField, 'label' => $nameLabel, 'value' => $model->{$nameField}, 'required' => true])
