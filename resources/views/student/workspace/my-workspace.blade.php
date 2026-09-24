@extends('backend.layouts.app')
@section('content')

<div class="content-wrapper">

    <!-- Main content -->
    <section>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">My Workspace</h3>
                <div class="btns-group">
                 </div>
            </div>
            <div class="card-body">
                @if($subcategories->isEmpty())
                    <div class="card-header">
                        <h3 class="card-title">No Data Found!</h3>
                    </div>
                @else
                <div class="row malti-color-list boxes">
                    @foreach($subcategories as $subcategory)
                    <div class="col-lg-4 col-sm-6 color-box mt-2 mb-2">
                        <div class="box @if(!$subcategory->hasAccess) stream-list-lock @endif">
                            <div class="img-shape">
                                <img src="{{ asset($subcategory->image) }}" alt="My Workspace" />
                            </div>
                            <div class="mt-4">
                                <h2 class=" sub-cate-name">{{ $subcategory->name }}</h2>
                                <p class="sub-cate-des mb-0">{{ $subcategory->description }}</p>
                            </div>
                            <div class="mt-4">
                                @if($subcategory->hasAccess && $subcategory->tool && $subcategory->tool->url)
                                <a href="@if(in_array($studentId,[72,834])){{ route($subcategory->tool->url) }}@else # @endif" class="btn malti-color-button-box" @if(!in_array($studentId,[72,834])) data-toggle="modal" data-target="#toolModal" @endif>
                                    View Content <i class="fa fa-arrow-right"></i>
                                </a>
                                @else
                                <a href="#" class="btn malti-color-button-box" data-toggle="modal" data-target="#toolModal">
                                    View Content <i class="fa fa-arrow-right"></i>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </section>
</div>
<!-- AI Tool Popup Modal -->
<div class="modal fade" id="toolModal" tabindex="-1" role="dialog" aria-labelledby="aiToolModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body text-center">
        <p>This tool is a secret waiting for you! Unlock it by earning wings.</p>
        <button type="button" class="btn btn-orange mt-3" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function matchCardHeights(selector) {
    const cards = document.querySelectorAll(selector);
    if (!cards.length) return;
    // Reset heights
    cards.forEach(card => card.style.minHeight = "");
    let rows = [];
    const tolerance = 2; // pixel tolerance for responsive layouts
    cards.forEach(card => {
        const top = card.getBoundingClientRect().top;
        let foundRow = rows.find(row => Math.abs(row.top - top) <= tolerance);
        if (!foundRow) {
            foundRow = { top: top, cards: [] };
            rows.push(foundRow);
        }
        foundRow.cards.push(card);
    });
    rows.forEach(row => {
        let maxHeight = 0;
        row.cards.forEach(card => {
            maxHeight = Math.max(maxHeight, card.offsetHeight);
        });
        row.cards.forEach(card => {
            card.style.minHeight = maxHeight + "px";
        });
    });
}
function initMatchHeights() {
    matchCardHeights(".color-box .sub-cate-name");
    matchCardHeights(".color-box .sub-cate-des");
}
let resizeTimer;
window.addEventListener("load", initMatchHeights);
window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(initMatchHeights, 150);
});
</script>
@endsection