@props(['product'])

@php
    $placeholder = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MDAiIGhlaWdodD0iNDAwIiB2aWV3Qm94PSIwIDAgNDAwIDQwMCI+PHJlY3Qgd2lkdGg9IjQwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiNmOGZhZmMiLz48cmVjdCB4PSIxNTAiIHk9IjE0MCIgd2lkdGg9IjEwMCIgaGVpZ2h0PSI4MCIgcng9IjgiIGZpbGw9IiNlNWU3ZWIiLz48Y2lyY2xlIGN4PSIxNzUiIGN5PSIxNjUiIHI9IjE1IiBmaWxsPSIjZDFkNWRiIi8+PHBhdGggZD0iTTE1MCAxOTAgTDE3MCAxNjUgTDE5MCAxODUgTDIwNSAxNzAgTDI1MCAxOTAiIHN0cm9rZT0iI2QxZDVkYiIgZmlsbD0ibm9uZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNjUlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTMiIGZpbGw9IiNhZmI0YmMiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
    $mainImage = $product->mainImageUrl() ?? $placeholder;
    $hoverImage = $product->images->filter(fn ($img) => $img->role !== 'main' && $img->publicUrl())->sortBy('sort_order')->first()?->publicUrl();
    $colorVariants = $product->variants->filter(fn ($v) => filled($v->color))->unique('color')->values();
    $displayColors = $colorVariants->take(5);
    $extraColorCount = max($colorVariants->count() - 5, 0);
    $ribbon = $product->variants->first(fn ($v) => filled($v->ribbon))?->ribbon;
    $sizes = $product->variants->filter(fn ($v) => filled($v->size))->pluck('size')->unique()->values();
@endphp

<article style="background:#fff;border-radius:18px;border:1.5px solid #e5e7eb;overflow:hidden;transition:transform .25s,box-shadow .25s;height:100%;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 16px 40px rgba(0,0,0,.1)';this.style.borderColor='#c7d2fe'" onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='#e5e7eb'">
    <a href="{{ route('products.show', $product) }}" style="text-decoration:none;display:flex;flex-direction:column;height:100%;">

        {{-- Image --}}
        <div style="position:relative;aspect-ratio:1/1;overflow:hidden;background:#f8fafc;">
            <img
                src="{{ $mainImage }}"
                alt="{{ $product->name }}"
                loading="lazy"
                style="width:100%;height:100%;object-fit:cover;transition:transform .4s,opacity .4s;"
                onerror="this.onerror=null;this.src='{{ $placeholder }}'"
                onmouseover="this.style.transform='scale(1.06)'"
                onmouseout="this.style.transform=''"
                class="card-main-img"
            >

            @if($hoverImage)
                <img
                    src="{{ $hoverImage }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .4s;"
                    onerror="this.onerror=null;this.style.display='none'"
                    class="card-hover-img"
                >
            @endif

            @if($ribbon)
                <span style="position:absolute;left:0;top:12px;background:#4f46e5;color:#fff;font-size:10px;font-weight:800;padding:4px 10px;border-radius:0 8px 8px 0;text-transform:uppercase;letter-spacing:.06em;">
                    {{ $ribbon }}
                </span>
            @endif

            {{-- Quick view overlay --}}
            <div style="position:absolute;inset-x:0;bottom:0;padding:10px;background:linear-gradient(to top,rgba(0,0,0,.5),transparent);opacity:0;transition:opacity .3s;" class="card-overlay">
                <div style="background:rgba(255,255,255,.95);border-radius:8px;padding:7px;text-align:center;font-size:11px;font-weight:700;color:#111827;">
                    View Product
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div style="padding:14px 16px 16px;display:flex;flex-direction:column;flex:1;gap:0;">

            @if($product->brand)
                <span style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#4f46e5;">
                    {{ $product->brand->name }}
                </span>
            @endif

            <h3 style="font-size:14px;font-weight:700;color:#111827;line-height:1.4;margin:5px 0 10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:40px;flex:1;">
                {{ $product->name }}
            </h3>

            @if($product->min_msrp)
                <div style="margin-bottom:10px;display:flex;align-items:baseline;gap:6px;flex-wrap:wrap;">
                    <span style="font-size:16px;font-weight:900;color:#111827;">
                        From ${{ number_format($product->min_msrp, 2) }}
                    </span>
                    @if($product->max_msrp && $product->max_msrp > $product->min_msrp)
                        <span style="font-size:12px;color:#9ca3af;text-decoration:line-through;">
                            ${{ number_format($product->max_msrp, 2) }}
                        </span>
                    @endif
                </div>
            @endif

            {{-- Color swatches --}}
            @if($displayColors->isNotEmpty())
                <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;margin-bottom:8px;">
                    @foreach($displayColors as $variant)
                        <span
                            title="{{ $variant->color }}"
                            style="width:16px;height:16px;border-radius:50%;border:1.5px solid rgba(0,0,0,.1);box-shadow:inset 0 0 0 1px rgba(255,255,255,.3);background-color:{{ $variant->color_hex_value ?: '#d1d5db' }};flex-shrink:0;"
                        ></span>
                    @endforeach
                    @if($extraColorCount > 0)
                        <span style="font-size:10px;font-weight:700;color:#6b7280;background:#f3f4f6;border-radius:100px;padding:1px 6px;">+{{ $extraColorCount }}</span>
                    @endif
                </div>
            @endif

            {{-- Size chips --}}
            @if($sizes->isNotEmpty())
                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                    @foreach($sizes->take(5) as $size)
                        <span style="border:1px solid #e5e7eb;border-radius:6px;padding:2px 7px;font-size:10px;font-weight:600;color:#6b7280;background:#f9fafb;">
                            {{ $size }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div style="margin-top:auto;padding-top:12px;">
                <div
                    style="width:100%;background:#4f46e5;color:#fff;border-radius:10px;padding:9px 0;text-align:center;font-size:12px;font-weight:700;letter-spacing:.03em;transition:background .2s;"
                    onmouseover="this.style.background='#4338ca'"
                    onmouseout="this.style.background='#4f46e5'"
                >
                    View Product →
                </div>
            </div>

        </div>
    </a>
</article>

<style>
    article:hover .card-overlay { opacity: 1 !important; }
    article:hover .card-hover-img { opacity: 1 !important; }
    article:hover .card-main-img { opacity: 0; }
</style>
