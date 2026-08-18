<?php
declare(strict_types=1);

namespace ZxArt\ZxProdCategories;

/**
 * Ids of the categories the code depends on.
 *
 * The catalogue is a tree, and the three top-level sections below are what the
 * site navigates and filters by. **Filtering by a section always means the whole
 * subtree**, resolved through {@see ProdCategoryTreeService}: a megademo is
 * demoscene, an arcade game is a game, a magazine issue is press. Matching a
 * section's own link alone silently drops most of what belongs to it.
 *
 * The remaining cases are leaves *inside* those sections. They exist to file a
 * production under a precise category — imports and one-off jobs assign them —
 * and must never be used as a filter root.
 */
enum CategoryIds: int
{
    // Top-level sections. These are the filter roots.
    case GAMES = 92177;
    case DEMOSCENE = 204819;
    case PRESS = 244858;
    case SYSTEM_SOFTWARE = 92183;
    case EDUCATIONAL = 92534;
    case SERIES = 551860;
    case MISC = 92188;

    // Leaves inside the sections above. For assignment, never for filtering.
    case DEMOS = 92159;
    case MEGADEMO = 92160;
    case TRACKMO = 92161;
    case PRESS_MAGAZINES = 92179;
    case COMPILATION_EDUCATIONAL = 202589;
    case COMPILATION_GAMES = 202590;
    case COMPILATION_MAGAZINES = 202591;
    case COMPILATION_DEMOS = 202592;
    case COMPILATION_UTILITIES = 202593;
    case COMPILATION_COVERTAPE = 92533;
}
