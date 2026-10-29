<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Turns the flat organization chart stored in the CMS into a nested tree for display.
 *
 * Each stored node has an `id`, a `name`, a `position`, an optional `parent_id` (who it reports to), a `placement`
 * ("below" its parent, or "side" for a card shown beside the parent, like a staff function) and a `sort_order`.
 */
class OrgChart
{
    public const PLACEMENT_BELOW = 'below';

    public const PLACEMENT_SIDE = 'side';

    /**
     * Build the tree of root nodes. Nodes without a name are ignored, a node whose parent does not exist becomes a
     * root, a node reporting to a side card is attached to that card's parent instead, and reporting loops are cut.
     *
     * @param  array<string, mixed>|null  $chart
     * @return array<int, array{id: string, name: string, position: string, photo: string|null, depth: int, children: array<int, mixed>, side: array<int, mixed>}>
     */
    public static function tree(?array $chart): array
    {
        $nodes = collect($chart['nodes'] ?? [])
            ->filter(fn (mixed $node): bool => is_array($node) && filled($node['id'] ?? null) && filled($node['name'] ?? null))
            ->unique('id')
            ->sortBy(fn (array $node): int => (int) ($node['sort_order'] ?? 0))
            ->keyBy('id');

        $childrenOf = [];

        foreach ($nodes as $id => $node) {
            $childrenOf[self::effectiveParent((string) $id, $nodes) ?? ''][] = (string) $id;
        }

        $build = function (string $id, int $depth, array $path) use (&$build, $nodes, $childrenOf): array {
            $below = [];
            $side = [];

            foreach ($childrenOf[$id] ?? [] as $childId) {
                if (in_array($childId, $path, true)) {
                    continue;
                }

                if (self::placement($nodes[$childId]) === self::PLACEMENT_SIDE) {
                    $side[] = self::card($nodes[$childId], $depth);
                } else {
                    $below[] = $build($childId, $depth + 1, [...$path, $id]);
                }
            }

            return [...self::card($nodes[$id], $depth), 'children' => $below, 'side' => $side];
        };

        return array_map(
            fn (string $id): array => $build($id, 0, []),
            $childrenOf[''] ?? [],
        );
    }

    /**
     * The node a given node hangs below: its parent, skipping over side cards, or null for a root.
     *
     * @param  Collection<string, array<string, mixed>>  $nodes
     */
    private static function effectiveParent(string $id, Collection $nodes): ?string
    {
        $parent = $nodes[$id]['parent_id'] ?? null;

        for ($guard = 0; filled($parent) && $guard < 50; $guard++) {
            $parent = (string) $parent;

            if ($parent === $id || ! $nodes->has($parent)) {
                return null;
            }

            if (self::placement($nodes[$parent]) !== self::PLACEMENT_SIDE) {
                return $parent;
            }

            $parent = $nodes[$parent]['parent_id'] ?? null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    private static function placement(array $node): string
    {
        return ($node['placement'] ?? self::PLACEMENT_BELOW) === self::PLACEMENT_SIDE ? self::PLACEMENT_SIDE : self::PLACEMENT_BELOW;
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array{id: string, name: string, position: string, photo: string|null, depth: int, children: array<int, mixed>, side: array<int, mixed>}
     */
    private static function card(array $node, int $depth): array
    {
        return [
            'id' => (string) $node['id'],
            'name' => (string) $node['name'],
            'position' => (string) ($node['position'] ?? ''),
            'photo' => MediaUrl::resolve($node['photo'] ?? null),
            'depth' => $depth,
            'children' => [],
            'side' => [],
        ];
    }
}
