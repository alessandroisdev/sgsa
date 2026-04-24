<?php
$models = glob('app/Models/*.php');
foreach ($models as $model) {
    $content = file_get_contents($model);
    $uses = [];
    if (!str_contains($content, 'use Illuminate\Database\Eloquent\Concerns\HasUuids;')) {
        $content = preg_replace('/(use Illuminate\\\\Database\\\\Eloquent\\\\Model;)/', "$1\nuse Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;", $content);
        $uses[] = 'HasUuids';
    }
    if (!str_contains($content, 'use Illuminate\Database\Eloquent\SoftDeletes;')) {
        if (str_contains($content, 'class Setting') === false && str_contains($content, 'class User') === false) {
             $content = preg_replace('/(use Illuminate\\\\Database\\\\Eloquent\\\\Concerns\\\\HasUuids;)/', "$1\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;", $content);
             $uses[] = 'SoftDeletes';
        }
    }
    
    // For User model
    if (str_contains($content, 'class User extends Authenticatable')) {
        if (!str_contains($content, 'use Illuminate\Database\Eloquent\Concerns\HasUuids;')) {
            $content = preg_replace('/(use Illuminate\\\\Foundation\\\\Auth\\\\User as Authenticatable;)/', "$1\nuse Illuminate\\Database\\Eloquent\\Concerns\\HasUuids;\nuse Illuminate\\Database\\Eloquent\\SoftDeletes;", $content);
        }
        $content = preg_replace('/use HasFactory, Notifiable;/', 'use HasFactory, Notifiable, HasUuids, SoftDeletes;', $content);
    } else {
        if (count($uses) > 0) {
            $traits = implode(', ', $uses);
            $content = preg_replace('/\{\n    \/\//', "{\n    use $traits;\n\n    protected \$guarded = [];\n", $content);
        }
    }
    file_put_contents($model, $content);
}
echo "Models updated successfully.\n";
