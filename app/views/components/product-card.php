<?php

/**
 * ============================================================================
 * WHFood - Product Card Component
 * ============================================================================
 * 
 * Komponen reusable untuk menampilkan product card.
 * Mendukung berbagai state: verified, bestseller, new, sold out, discount.
 * 
 * @package     WHFood
 * @author      WHFood Development Team
 * @version     1.0.0
 * @since       2026-01-12
 * 
 * @param array $product {
 *     @type int    $id              Product ID
 *     @type string $name            Product name
 *     @type string $description     Product description
 *     @type string $image           Product image URL
 *     @type float  $price           Original price
 *     @type float  $discountPrice   Discounted price (optional)
 *     @type int    $discountPercent Discount percentage (optional)
 *     @type float  $rating          Product rating (0-5)
 *     @type bool   $isAvailable     Availability status
 *     @type bool   $isFeatured      Featured/bestseller status
 *     @type bool   $isNew           New product flag
 *     @type array  $seller {
 *         @type string $name        Seller name
 *         @type string $avatar      Seller avatar URL
 *         @type bool   $isVerified  Verification status
 *         @type string $whatsapp    WhatsApp number
 *     }
 * }
 */

declare(strict_types=1);

/**
 * Render a product card
 * 
 * @param array $product Product data
 * @param int   $delay   AOS animation delay (ms)
 * @return string HTML output
 */
function renderProductCard(array $product, int $delay = 0): string
{
    // Extract product data with defaults
    $id = $product['id'] ?? 0;
    $name = htmlspecialchars($product['name'] ?? 'Produk');
    $description = htmlspecialchars($product['description'] ?? '');
    $image = $product['image'] ?? 'https://via.placeholder.com/400';
    $price = $product['price'] ?? 0;
    $discountPrice = $product['discountPrice'] ?? null;
    $discountPercent = $product['discountPercent'] ?? null;
    $rating = $product['rating'] ?? 0;
    $isAvailable = $product['isAvailable'] ?? true;
    $isFeatured = $product['isFeatured'] ?? false;
    $isNew = $product['isNew'] ?? false;
    
    // Seller info
    $sellerName = htmlspecialchars($product['seller']['name'] ?? 'Penjual');
    $sellerAvatar = $product['seller']['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($sellerName);
    $isVerified = $product['seller']['isVerified'] ?? false;
    $whatsapp = $product['seller']['whatsapp'] ?? '';
    
    // Format prices
    $formattedPrice = 'Rp' . number_format($price, 0, ',', '.');
    $formattedDiscountPrice = $discountPrice ? 'Rp' . number_format($discountPrice, 0, ',', '.') : null;
    
    // Generate WhatsApp link
    $waMessage = urlencode("Halo, saya mau pesan {$name}");
    $waLink = "https://wa.me/{$whatsapp}?text={$waMessage}";
    
    // Card opacity class for sold out items
    $cardOpacityClass = $isAvailable ? '' : 'opacity-60';
    $imageGrayscale = $isAvailable ? '' : 'grayscale-[30%]';
    
    ob_start();
    ?>
    <article class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden transform hover:scale-105"
             data-aos="fade-up" 
             data-aos-delay="<?= $delay ?>">
        
        <!-- Image Container -->
        <div class="relative food-img-container aspect-square overflow-hidden">
            <img src="<?= $image ?>" 
                 alt="<?= $name ?>" 
                 class="food-img w-full h-full object-cover <?= $imageGrayscale ?>"
                 loading="lazy">
            
            <?php if ($isAvailable): ?>
                <!-- Gradient Overlay on Hover -->
                <div class="absolute inset-0 food-gradient opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <?php if ($isVerified): ?>
                    <!-- Verified Badge -->
                    <div class="badge-verified absolute top-3 left-3 flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 text-white text-xs font-semibold rounded-full shadow-lg">
                        <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                        Terverifikasi
                    </div>
                <?php elseif ($isFeatured): ?>
                    <!-- Best Seller Badge -->
                    <div class="absolute top-3 left-3 flex items-center gap-1.5 px-3 py-1.5 bg-accent-500 text-white text-xs font-semibold rounded-full shadow-lg">
                        <i data-lucide="flame" class="w-3.5 h-3.5"></i>
                        Best Seller
                    </div>
                <?php endif; ?>
                
                <?php if ($discountPercent): ?>
                    <!-- Discount Badge -->
                    <div class="absolute top-3 right-3 px-2.5 py-1 bg-accent-500 text-white text-xs font-bold rounded-lg shadow-lg">
                        -<?= $discountPercent ?>%
                    </div>
                <?php elseif ($isNew): ?>
                    <!-- New Badge -->
                    <div class="absolute top-3 right-3 px-2.5 py-1 bg-blue-500 text-white text-xs font-bold rounded-lg shadow-lg">
                        BARU
                    </div>
                <?php endif; ?>
                
                <!-- Favorite Button -->
                <button class="absolute bottom-3 right-3 w-10 h-10 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0"
                        aria-label="Tambah ke favorit">
                    <i data-lucide="heart" class="w-5 h-5 text-gray-600 hover:text-red-500 transition-colors"></i>
                </button>
            <?php else: ?>
                <!-- Sold Out Overlay -->
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                    <span class="px-6 py-3 bg-gray-900/80 text-white font-bold text-lg rounded-xl">
                        HABIS
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Card Content -->
        <div class="p-5 <?= $cardOpacityClass ?>">
            <!-- Seller Info -->
            <div class="flex items-center gap-2 mb-3">
                <img src="<?= $sellerAvatar ?>" 
                     alt="<?= $sellerName ?>" 
                     class="w-6 h-6 rounded-full"
                     loading="lazy">
                <span class="text-sm text-gray-500 truncate"><?= $sellerName ?></span>
                <div class="flex items-center gap-1 ml-auto flex-shrink-0">
                    <i data-lucide="star" class="w-4 h-4 text-accent-500 fill-accent-500"></i>
                    <span class="text-sm font-medium text-gray-700"><?= number_format($rating, 1) ?></span>
                </div>
            </div>
            
            <!-- Product Name -->
            <h3 class="font-bold text-gray-900 text-lg mb-2 line-clamp-2 group-hover:text-primary-600 transition-colors">
                <?= $name ?>
            </h3>
            
            <!-- Product Description -->
            <?php if ($description): ?>
                <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                    <?= $description ?>
                </p>
            <?php endif; ?>
            
            <!-- Price & CTA -->
            <div class="flex items-end justify-between">
                <div>
                    <?php if ($formattedDiscountPrice): ?>
                        <span class="text-xs text-gray-400 line-through"><?= $formattedPrice ?></span>
                        <div class="text-xl font-bold text-primary-600"><?= $formattedDiscountPrice ?></div>
                    <?php else: ?>
                        <div class="text-xl font-bold <?= $isAvailable ? 'text-primary-600' : 'text-gray-400' ?>">
                            <?= $formattedPrice ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($isAvailable && $whatsapp): ?>
                    <!-- WhatsApp Order Button -->
                    <a href="<?= $waLink ?>" 
                       target="_blank"
                       rel="noopener noreferrer"
                       class="whatsapp-btn flex items-center gap-2 px-4 py-2.5 text-white font-semibold rounded-xl">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        <span class="hidden sm:inline">Pesan</span>
                    </a>
                <?php else: ?>
                    <!-- Sold Out Button -->
                    <button disabled 
                            class="flex items-center gap-2 px-4 py-2.5 bg-gray-300 text-gray-500 font-semibold rounded-xl cursor-not-allowed">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                        <span class="hidden sm:inline">Habis</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

/**
 * Render multiple product cards in a grid
 * 
 * @param array $products Array of product data
 * @param int   $columns  Number of grid columns (default: 4)
 * @return string HTML output
 */
function renderProductGrid(array $products, int $columns = 4): string
{
    $gridCols = [
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
        4 => 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
    ];
    
    $colClass = $gridCols[$columns] ?? $gridCols[4];
    
    ob_start();
    ?>
    <div class="grid <?= $colClass ?> gap-6">
        <?php foreach ($products as $index => $product): ?>
            <?= renderProductCard($product, ($index + 1) * 100) ?>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
