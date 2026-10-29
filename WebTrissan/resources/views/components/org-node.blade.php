@props(['node'])
{{--
    One person in the organization tree. Side cards sit beside the card with a horizontal connector,
    and the people reporting to this node are rendered below by this same component.
--}}
<li>
  <div class="org-wrap">
    <div class="org-card org-d{{ min($node['depth'], 4) }}">
      @if($node['photo'])
        <img src="{{ $node['photo'] }}" alt="{{ $node['name'] }}" class="org-photo" loading="lazy">
      @endif
      <span class="org-name">{{ $node['name'] }}</span>
      @if(filled($node['position']))
        <span class="org-position">{{ $node['position'] }}</span>
      @endif
    </div>
    @foreach($node['side'] as $sideNode)
      <div class="org-side">
        <span class="org-side-line"></span>
        <div class="org-card org-d{{ min($sideNode['depth'], 4) }}">
          @if($sideNode['photo'])
            <img src="{{ $sideNode['photo'] }}" alt="{{ $sideNode['name'] }}" class="org-photo" loading="lazy">
          @endif
          <span class="org-name">{{ $sideNode['name'] }}</span>
          @if(filled($sideNode['position']))
            <span class="org-position">{{ $sideNode['position'] }}</span>
          @endif
        </div>
      </div>
    @endforeach
  </div>
  @if(count($node['children']) > 0)
    <ul>
      @foreach($node['children'] as $child)
        <x-org-node :node="$child" />
      @endforeach
    </ul>
  @endif
</li>
