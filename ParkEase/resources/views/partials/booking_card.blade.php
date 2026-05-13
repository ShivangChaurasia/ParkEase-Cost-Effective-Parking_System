<div class="col-md-6 col-lg-4 booking-card-wrapper">
    <div class="booking-card-premium h-100 shadow-sm d-flex flex-column" style="background: var(--glass-card); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.4); border-radius: 28px; overflow: hidden;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">{{ $booking->parkingLot->name ?? 'Unknown' }}</h5>
                    <p class="text-muted small mb-0"><i class="bi bi-geo-alt-fill text-primary"></i> {{ $booking->parkingLot->city ?? '' }}</p>
                </div>
                
                @php
                    $statusClass = 'status-' . $type;
                    $statusLabel = ucfirst($type);
                    if ($type === 'active') $statusLabel = 'Live Now';
                @endphp
                <div class="status-badge-animated {{ $statusClass }}">
                    @if($type === 'active') <div class="pulse-dot"></div> @endif
                    {{ $statusLabel }}
                </div>
            </div>

            <div class="d-flex align-items-center mb-4 p-3 rounded-4 bg-white bg-opacity-50 border shadow-sm">
                <div class="flex-shrink-0 me-3">
                    <div class="bg-dark text-white rounded-4 d-flex align-items-center justify-content-center shadow" style="width: 50px; height: 50px;">
                        <h4 class="mb-0 fw-bold">{{ $booking->slot->slot_number ?? '?' }}</h4>
                    </div>
                </div>
                <div>
                    <div class="small text-muted text-uppercase fw-bold ls-1" style="font-size: 0.6rem;">{{ strtoupper($booking->vehicle_type ?? 'CAR') }}</div>
                    <div class="fw-bold text-dark">₹{{ $booking->price }}</div>
                </div>
                @if($type === 'upcoming')
                    <div class="ms-auto">
                        @php
                            $startTimeStr = explode('-', $booking->time_slot_id)[0];
                            $start = \Carbon\Carbon::parse($booking->date . ' ' . $startTimeStr);
                            $minsDiff = now()->diffInMinutes($start, false);
                        @endphp
                        <span class="countdown-mini">
                            <i class="bi bi-clock me-1"></i> In {{ $minsDiff > 60 ? floor($minsDiff/60).'h '.($minsDiff%60).'m' : $minsDiff.'m' }}
                        </span>
                    </div>
                @endif
            </div>

            <div class="row g-2 mb-0">
                <div class="col-6">
                    <div class="p-2 border rounded-3 text-center bg-white bg-opacity-30">
                        <div class="small text-muted mb-1" style="font-size: 0.6rem;">DATE</div>
                        <div class="fw-bold small">{{ \Carbon\Carbon::parse($booking->date)->format('D, M d') }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded-3 text-center bg-white bg-opacity-30">
                        <div class="small text-muted mb-1" style="font-size: 0.6rem;">TIME SLOT</div>
                        <div class="fw-bold small text-nowrap">{{ $booking->time_slot_id }}</div>
                    </div>
                </div>
            </div>
            
            @if($type === 'cancelled')
                <div class="mt-3 p-2 rounded-3 bg-danger bg-opacity-5 border border-danger border-opacity-10">
                    <div class="d-flex justify-content-between small">
                        <span class="text-danger fw-bold">Refund:</span>
                        <span class="text-dark fw-bold">₹{{ $booking->refund_amount ?? 0 }} ({{ $booking->refund_status ?? 'N/A' }})</span>
                    </div>
                </div>
            @endif
        </div>

        <div class="card-footer-actions mt-auto" style="background: rgba(0,0,0,0.02); padding: 20px; border-top: 1px solid rgba(0,0,0,0.05); display: flex; gap: 10px;">
            @if($type === 'active')
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $booking->parkingLot->latitude }},{{ $booking->parkingLot->longitude }}" target="_blank" class="action-btn action-btn-secondary" style="flex: 1; padding: 10px; border-radius: 14px; font-weight: 700; font-size: 0.85rem; border: 1.5px solid #eee; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; color: #000;">
                    <i class="bi bi-geo-alt"></i> Navigate
                </a>
                <button class="action-btn action-btn-primary" style="flex: 1; padding: 10px; border-radius: 14px; font-weight: 700; font-size: 0.85rem; border: none; background: #000; color: #fff; display: flex; align-items: center; justify-content: center; gap: 8px;" data-bs-toggle="modal" data-bs-target="#qrModal">
                    <i class="bi bi-qr-code"></i> Entry QR
                </button>
            @elseif($type === 'upcoming')
                @php
                    $startTimeStr = explode('-', $booking->time_slot_id)[0];
                    $start = \Carbon\Carbon::parse($booking->date . ' ' . $startTimeStr);
                    $minsDiff = now()->diffInMinutes($start, false);
                    $refundPreview = $minsDiff >= 120 ? $booking->price : ($minsDiff >= 30 ? $booking->price * 0.5 : 0);
                @endphp
                <button class="action-btn action-btn-secondary" style="flex: 1; padding: 10px; border-radius: 14px; font-weight: 700; font-size: 0.85rem; border: 1.5px solid #eee; display: flex; align-items: center; justify-content: center; gap: 8px;" onclick="openCancelModal('{{ $booking->_id }}', '{{ $booking->parkingLot->name }}', '{{ $booking->slot->slot_number }}', '{{ $booking->price }}', '{{ $refundPreview }}')">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <div class="dropdown" style="flex: 1;">
                    <button class="action-btn action-btn-primary w-100 h-100" style="padding: 10px; border-radius: 14px; font-weight: 700; font-size: 0.85rem; border: none; background: #000; color: #fff; display: flex; align-items: center; justify-content: center; gap: 8px;" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-ticket-perforated"></i> Ticket
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li><a class="dropdown-item py-2" href="{{ route('invoice.view', $booking->_id) }}" target="_blank"><i class="bi bi-eye me-2 text-primary"></i>View Ticket</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('invoice.download', $booking->_id) }}"><i class="bi bi-download me-2 text-success"></i>Download PDF</a></li>
                    </ul>
                </div>
            @elseif($type === 'completed' || $type === 'cancelled')
                <a href="/parking/{{ $booking->parking_lot_id }}" class="action-btn action-btn-primary" style="flex: 1; padding: 10px; border-radius: 14px; font-weight: 700; font-size: 0.85rem; border: none; background: #000; color: #fff; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;">
                    <i class="bi bi-arrow-repeat"></i> Rebook
                </a>
                <div class="dropdown" style="flex: 1;">
                    <button class="action-btn action-btn-secondary w-100 h-100" style="padding: 10px; border-radius: 14px; font-weight: 700; font-size: 0.85rem; border: 1.5px solid #eee; display: flex; align-items: center; justify-content: center; gap: 8px;" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-file-earmark-pdf"></i> Invoice
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li><a class="dropdown-item py-2" href="{{ route('invoice.view', $booking->_id) }}" target="_blank"><i class="bi bi-eye me-2 text-primary"></i>View</a></li>
                        <li><a class="dropdown-item py-2" href="{{ route('invoice.download', $booking->_id) }}"><i class="bi bi-download me-2 text-success"></i>Download</a></li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
