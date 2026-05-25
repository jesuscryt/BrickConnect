{{-- Formulario para crear una oferta de empleo --}}
@extends('layouts.app')

@section('titulo', 'Publicar Oferta - BrickConnect')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-md-7">

        <h3 class="fw-bold mb-4"><i class="bi bi-plus-circle text-warning"></i> Publicar Oferta de Empleo</h3>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('ofertas.store') }}">
                    @csrf

                    {{-- Título del puesto --}}
                    <div class="mb-3">
                        <label for="titulo" class="form-label fw-bold">Título del puesto</label>
                        <input type="text" name="titulo" id="titulo"
                               class="form-control @error('titulo') is-invalid @enderror"
                               value="{{ old('titulo') }}"
                               placeholder="Ej: Jefe de Obra, Albañil, Electricista..." required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Empresa --}}
                    <div class="mb-3">
                        <label for="empresa" class="form-label fw-bold">Empresa</label>
                        <input type="text" name="empresa" id="empresa"
                               class="form-control @error('empresa') is-invalid @enderror"
                               value="{{ old('empresa') }}"
                               placeholder="Nombre de la empresa" required>
                        @error('empresa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ubicación --}}
                    <div class="mb-3">
                        <label for="ubicacion" class="form-label fw-bold">Ubicación</label>
                        <input type="text" name="ubicacion" id="ubicacion"
                               class="form-control @error('ubicacion') is-invalid @enderror"
                               value="{{ old('ubicacion') }}"
                               placeholder="Ej: Madrid, Barcelona, Sevilla..." required>
                        @error('ubicacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo de contrato --}}
                    <div class="mb-3">
                        <label for="tipo_contrato" class="form-label fw-bold">Tipo de contrato</label>
                        <select name="tipo_contrato" id="tipo_contrato" class="form-select" required>
                            <option value="Tiempo completo" {{ old('tipo_contrato') == 'Tiempo completo' ? 'selected' : '' }}>
                                Tiempo completo
                            </option>
                            <option value="Media jornada" {{ old('tipo_contrato') == 'Media jornada' ? 'selected' : '' }}>
                                Media jornada
                            </option>
                            <option value="Temporal" {{ old('tipo_contrato') == 'Temporal' ? 'selected' : '' }}>
                                Temporal
                            </option>
                            <option value="Por obra" {{ old('tipo_contrato') == 'Por obra' ? 'selected' : '' }}>
                                Por obra
                            </option>
                        </select>
                    </div>

                    {{-- Horas semanales (opcional) --}}
                    <div class="mb-3">
                        <label for="horas_semanales" class="form-label fw-bold">Horas semanales <small class="text-muted">(opcional)</small></label>
                        <div class="input-group" style="max-width: 240px;">
                            <input type="number" name="horas_semanales" id="horas_semanales"
                                   class="form-control @error('horas_semanales') is-invalid @enderror"
                                   value="{{ old('horas_semanales') }}"
                                   placeholder="Ej: 40" min="1" max="168">
                            <span class="input-group-text">horas/semana</span>
                        </div>
                        @error('horas_semanales')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Salario (opcional) --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Salario <small class="text-muted">(opcional)</small></label>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input type="text" name="salario" id="salario"
                                           class="form-control @error('salario') is-invalid @enderror"
                                           value="{{ old('salario') ? number_format(old('salario'), 0, ',', '.') : '' }}"
                                           placeholder="Ej: 2.000" inputmode="numeric" autocomplete="off">
                                    <span class="input-group-text">€</span>
                                </div>
                                @error('salario')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4" id="salario_periodo_wrap">
                                <select name="salario_periodo" id="salario_periodo" class="form-select @error('salario_periodo') is-invalid @enderror">
                                    <option value="">-- Periodo --</option>
                                    <option value="por hora"  {{ old('salario_periodo') == 'por hora'  ? 'selected' : '' }}>Por hora</option>
                                    <option value="por día"   {{ old('salario_periodo') == 'por día'   ? 'selected' : '' }}>Por día</option>
                                    <option value="semanal"   {{ old('salario_periodo') == 'semanal'   ? 'selected' : '' }}>Semanal</option>
                                    <option value="mensual"   {{ old('salario_periodo') == 'mensual'   ? 'selected' : '' }}>Mensual</option>
                                    <option value="anual"     {{ old('salario_periodo') == 'anual'     ? 'selected' : '' }}>Anual</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="salario_tipo" class="form-select @error('salario_tipo') is-invalid @enderror">
                                    <option value="">-- Tipo --</option>
                                    <option value="bruto" {{ old('salario_tipo') == 'bruto' ? 'selected' : '' }}>Bruto</option>
                                    <option value="neto"  {{ old('salario_tipo') == 'neto'  ? 'selected' : '' }}>Neto</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Descripción --}}
                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-bold">Descripción del puesto</label>
                        <textarea name="descripcion" id="descripcion" rows="5"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  placeholder="Describe las funciones, requisitos y condiciones del puesto..." required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Botones --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('ofertas.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning fw-bold">
                            <i class="bi bi-check-lg"></i> Publicar Oferta
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@push('scripts')
<script>
    (function () {
        // --- Formato visual del salario ---
        const salarioInput = document.getElementById('salario');
        const form         = salarioInput.closest('form');

        function formatearSalario(valor) {
            // Quitar todo lo que no sea dígito
            const num = valor.replace(/\D/g, '');
            if (!num) return '';
            // Separador de miles con punto
            return parseInt(num, 10).toLocaleString('es-ES');
        }

        salarioInput.addEventListener('input', function () {
            const pos = this.selectionStart;
            const antesLen = this.value.length;
            this.value = formatearSalario(this.value);
            // Reajustar cursor
            const diff = this.value.length - antesLen;
            this.setSelectionRange(pos + diff, pos + diff);
        });

        // Antes de enviar, limpiar separadores para que el servidor reciba un número limpio
        form.addEventListener('submit', function () {
            salarioInput.value = salarioInput.value.replace(/\./g, '').replace(/,/g, '.');
        });

        // --- Ocultar periodo para "Por obra" ---
        const tipoContrato = document.getElementById('tipo_contrato');
        const periodosWrap  = document.getElementById('salario_periodo_wrap');
        const periodoSelect = document.getElementById('salario_periodo');

        function actualizarPeriodo() {
            if (tipoContrato.value === 'Por obra') {
                periodosWrap.style.display = 'none';
                periodoSelect.value = '';
                periodoSelect.name  = ''; // no enviar el campo
            } else {
                periodosWrap.style.display = '';
                periodoSelect.name = 'salario_periodo';
            }
        }

        tipoContrato.addEventListener('change', actualizarPeriodo);
        actualizarPeriodo(); // ejecutar al cargar por si hay old('tipo_contrato')
    })();
</script>
@endpush

@endsection
