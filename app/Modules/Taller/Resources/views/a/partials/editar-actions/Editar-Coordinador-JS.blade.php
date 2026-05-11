    @push('scripts')
    <script src="{{ asset('js/modules/taller/facilitadores.js') }}"></script>
    <script src="{{ asset('js/modules/taller/cursos.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            TallerModule.init({
                tiposEvaluacion: {!! $tiposEvaluacionJson !!},
                saveBtnId: 'btn-save-curso'
            });

            // Confirmación SweetAlert al guardar
            const form = document.querySelector('form[action*="cursos"]');
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    // ── Validación de ponderación 100% ──
                    let totalPonderacion = 0;
                    let tieneEvaluacion = false;
                    document.querySelectorAll('.contenido-item').forEach(item => {
                        const sw = item.querySelector('input[name*="[es_evaluacion]"][type="checkbox"]');
                        if (sw && sw.checked) {
                            tieneEvaluacion = true;
                            const inp = item.querySelector('input[name*="[ponderacion]"]');
                            totalPonderacion += parseFloat(inp?.value) || 0;
                        }
                    });

                    if (tieneEvaluacion && totalPonderacion !== 100) {
                        Swal.fire({
                            title: 'Balance de evaluación incorrecto',
                            html: `La ponderación total de las evaluaciones debe ser exactamente <b>100%</b>.<br>Actualmente es <b>${totalPonderacion}%</b>.`,
                            icon: 'warning',
                            confirmButtonColor: '#1572e8',
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '¿Guardar cambios?',
                        text: 'Se actualizarán los datos del curso y sus contenidos.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#1572e8',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fas fa-save me-1"></i> Sí, guardar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Guardando...',
                                text: 'Por favor espera.',
                                icon: 'info',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => Swal.showLoading()
                            });
                            form.submit();
                        }
                    });
                });
            }
        });

        // Proxies de compatibilidad
        function toggleEvaluacion(el) { TallerModule.toggleEvaluacion(el); }
        function toggleMaterial(el) { TallerModule.toggleMaterial(el); }
        function toggleLocalidades(el) { TallerModule.toggleLocalidades(el); }
        function filterFacilitators() { FacilitadorModule.filterFacilitators(); }
        
        function selectFacilitador(element, id, name, doc) { 
            document.querySelectorAll('.facilitator-item').forEach(item => {
                item.classList.remove('bg-primary-soft', 'border-primary', 'border-start', 'border-4');
                item.querySelector('.check-icon')?.classList.add('d-none');
            });
            element.classList.add('bg-primary-soft', 'border-primary', 'border-start', 'border-4');
            element.querySelector('.check-icon')?.classList.remove('d-none');
            FacilitadorModule.selectFacilitador(id, name, doc); 
        }

        // Proxy adicional para el nombre de función que espera el HTML antiguo del modal si aplica
        function selectFacilitator(element, id, name, doc) { selectFacilitador(element, id, name, doc); }
    </script>
    @endpush

    @push('styles')
        <style>
            .bg-primary-soft { background-color: rgba(30, 58, 138, 0.1); }
            .bg-success-light { background-color: rgba(16, 185, 129, 0.1); }
            .border-light-2 { border-color: #f1f4f8 !important; }
            .shadow-xs { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            .shadow-card { box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
            
            .content-card-edit {
                border: 1px solid #e2e8f0;
                border-left: 5px solid #1e3a8a;
                transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
                box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            }
            .content-card-edit:hover {
                transform: translateY(-4px);
                box-shadow: 0 15px 35px rgba(30, 58, 138, 0.12) !important;
                border-color: rgba(30, 58, 138, 0.2);
            }
            
            .focus-none:focus { outline: none; border: none; box-shadow: none; }
            .hover-opacity-100:hover { opacity: 1 !important; }
            .hvr-push { transition: transform 0.2s; }
            .hvr-push:active { transform: scale(0.98); }
            .transition-hover { transition: all 0.2s ease; }
            .transition-hover:hover { background-color: #f1f5f9; transform: translateY(-1px); }
            .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
            .progress-bar { transition: width 0.6s cubic-bezier(0.65, 0, 0.35, 1); }

            /* Custom Scrollbar */
            .custom-scrollbar::-webkit-scrollbar { width: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #999; }
        </style>
    @endpush

    <!-- Modal para Selección de Facilitadores -->
    <div class="modal fade" id="modalFacilitadores" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-0 bg-primary py-4 px-4">
                    <h5 class="modal-title fw-bold text-white"><i class="fas fa-user-graduate me-2"></i> Seleccionar Facilitador</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light bg-opacity-50">
                    <!-- Buscador -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted border-light-2"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control border-start-0 py-2 border-light-2" id="filtro_nombre_cedula_modal" onkeyup="filterFacilitators()" placeholder="Buscar por nombre o cédula...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <select id="filtro_especializacion_modal" class="form-select shadow-sm py-2 border-light-2" onchange="filterFacilitators()">
                                <option value="">Todas las especializaciones</option>
                                @foreach($especializaciones as $esp)
                                    <option value="{{ $esp->id }}">{{ $esp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Lista -->
                    <div id="facilitator-container" class="bg-white rounded-4 border border-light-2 overflow-auto shadow-sm" style="max-height: 400px;">
                        @foreach($facilitadores as $facilitador)
                            @php $p = $facilitador->personalData @endphp
                            <div class="facilitator-item p-3 border-bottom cursor-pointer transition-all hover-bg-light {{ old('id_persona', $curso->id_persona) == $p->id_persona ? 'bg-primary-soft border-primary border-start border-4' : '' }}"
                                onclick="selectFacilitador(this, '{{ $p->crypt_id }}', '{{ $p->primer_nombre }} {{ $p->primer_apellido }}', '{{ $p->dni }}')"
                                data-name="{{ strtolower($p->primer_nombre . ' ' . $p->primer_apellido) }}"
                                data-doc="{{ $p->dni }}"
                                data-specializations="{{ $p->especializaciones->pluck('id')->join(',') }}">
                                
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        {!! renderAvatar($p, 'avatar-sm') !!}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $p->primer_nombre }} {{ $p->primer_apellido }}</h6>
                                                <div class="small text-muted mt-1 d-flex align-items-center">
                                                    <i class="fas fa-id-card me-2 opacity-50"></i> C.I: {{ $p->dni }}
                                                </div>
                                            </div>
                                            <span class="badge bg-light text-primary border border-primary-soft small fw-normal rounded-pill px-3">
                                                Docente
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ms-3 check-icon {{ old('id_persona', $curso->id_persona) == $p->id_persona ? '' : 'd-none' }}">
                                        <i class="fas fa-check-circle text-primary fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
