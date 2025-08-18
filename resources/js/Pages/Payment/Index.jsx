import React, { useState, useEffect } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { PayPalScriptProvider, PayPalButtons, usePayPalScriptReducer } from "@paypal/react-paypal-js";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Card, CardContent } from "@/Components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import { useToast } from "@/Components/ui/use-toast";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { useTranslation } from 'react-i18next';
import {
    Coins,
    Gift,
    Sparkles,
    AlertCircle,
    Smartphone,
    CreditCard,
    Loader2
} from 'lucide-react';

const TOKEN_PACKS = [
    {
        id: 'starter',
        tokens: 10,
        bonusTokens: 0,
        priceEuros: 1,
        priceFCFA: 600,
        color: 'from-amber-400 to-amber-600',
        popularityText: 'starter'
    },
    {
        id: 'plus',
        tokens: 20,
        bonusTokens: 5,
        priceEuros: 2,
        priceFCFA: 1200,
        mostPopular: true,
        color: 'from-rose-400 to-rose-600',
        popularityText: 'plus'
    },
    {
        id: 'pro',
        tokens: 50,
        bonusTokens: 10,
        priceEuros: 5,
        priceFCFA: 3000,
        color: 'from-purple-400 to-purple-600',
        popularityText: 'plus'
    },
    {
        id: 'ultimate',
        tokens: 100,
        bonusTokens: 30,
        priceEuros: 10,
        priceFCFA: 6000,

        color: 'from-blue-400 to-blue-600',
        popularityText: 'ultimate'
    },

];

const PaymentMethodsInfo = {
    mobileMoneyLogos: [
        { name: 'MTN Mobile Money', logo: '/mtn-momo.png' },
        { name: 'Orange Money', logo: '/orange-money.png' }
    ],
    cardLogos: [
        // { name: 'Visa', logo: '/visa.png' },
        // { name: 'Mastercard', logo: '/mastercard.png' }
    ]
};

const NotchPayButton = ({ pack, onSuccess, auth }) => {
    const [loading, setLoading] = useState(false);
    const { toast } = useToast();
    const { t } = useTranslation();

    const handlePayment = async () => {
        try {
            setLoading(true);
            const response = await fetch('/api/notchpay/initialize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(document.cookie
                        .split('; ')
                        .find(row => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1] || ''),
                    'Accept': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({
                    tokens: pack.tokens + pack.bonusTokens,
                    amount: pack.priceFCFA,
                    email: auth?.user?.email || 'user@example.com'
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || t('payment.errors.default'));
            }

            const result = await response.json();

            if (result.success && result.authorization_url) {
                window.location.href = result.authorization_url;
            } else {
                throw new Error(t('payment.errors.initialization'));
            }

        } catch (error) {
            console.error('Erreur de paiement:', error);
            toast({
                variant: "destructive",
                title: t('payment.errors.paymentFailed'),
                description: error.message || t('payment.errors.processingError')
            });
        } finally {
            setLoading(false);
        }
    };

    return (
        <button
            onClick={handlePayment}
            disabled={loading}
            className={`w-full py-3 px-4 rounded-lg font-medium text-white bg-gradient-to-r from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900 transition-all duration-200 flex items-center justify-center gap-2 ${loading ? 'opacity-75 cursor-not-allowed' : ''}`}
        >
            {loading ? (
                <>
                    <Loader2 className="w-5 h-5 animate-spin" />
                    <span>{t('payment.processing')}</span>
                </>
            ) : (
                <>
                    <Smartphone className="w-5 h-5" />
                    <span>{t('payment.payWithMobile')}</span>
                </>
            )}
        </button>
    );
};

const PayPalPackButton = ({ pack, onSuccess }) => {
    const [{ isPending }] = usePayPalScriptReducer();
    const [error, setError] = useState(null);
    const { t } = useTranslation();

    const handlePayPalCapture = async (data, actions) => {
        try {
            const details = await actions.order.capture();
            const response = await fetch('/api/paypal/capture-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(document.cookie
                        .split('; ')
                        .find(row => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1] || ''),
                    'Accept': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({
                    orderID: data.orderID,
                    tokens: pack.tokens + pack.bonusTokens,
                    paypalDetails: details
                })
            });

            if (!response.ok) {
                throw new Error(t('payment.errors.captureFailure'));
            }

            const result = await response.json();
            if (result.success) {
                onSuccess(result.new_balance);
            } else {
                throw new Error(t('payment.errors.processingFailure'));
            }
        } catch (err) {
            console.error('Erreur lors de la capture PayPal:', err);
            setError(err.message || t('payment.errors.default'));
        }
    };

    if (isPending) {
        return (
            <div className="flex items-center justify-center h-12">
                <Loader2 className="w-6 h-6 animate-spin text-blue-500" />
            </div>
        );
    }

    return (
        <div className="space-y-3">
            {error && (
                <div className="text-red-500 text-sm p-2 bg-red-50 rounded-lg">
                    {error}
                </div>
            )}
            <PayPalButtons
                style={{
                    layout: "vertical",
                    shape: "rect",
                    color: "gold"
                }}
                createOrder={(data, actions) => {
                    // @ts-ignore
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: pack.priceEuros.toString(),
                                currency_code: "XAF"
                            },
                            description: `${pack.tokens + pack.bonusTokens} tokens`
                        }]
                    });
                }}
                onApprove={handlePayPalCapture}
                onError={(err) => {
                    console.error('Erreur PayPal:', err);
                    setError(t('payment.errors.paypalFailed'));
                }}
            />
        </div>
    );
};

function cn(...classes) {
    return classes.filter(Boolean).join(' ');
}

const PaymentTabs = ({ pack, onSuccess, auth }) => {
    const { t } = useTranslation();

    return (
        <Tabs defaultValue="mobile" className="w-full">
            <TabsList className="grid grid-cols-2 w-full">
                <TabsTrigger value="mobile" className="flex items-center gap-2">
                    <Smartphone className="w-4 h-4" />
                    <span>{t('payment.tabs.mobileMoney')}</span>
                </TabsTrigger>
                <TabsTrigger value="card" className="flex items-center gap-2">
                    <CreditCard className="w-4 h-4" />
                    <span>{t('payment.tabs.bankCard')}</span>
                </TabsTrigger>
            </TabsList>

            <TabsContent value="mobile" className="mt-4 space-y-4">
                <div className="text-2xl font-bold text-center">
                    {pack.priceFCFA.toLocaleString()} FCFA
                </div>
                <NotchPayButton pack={pack} onSuccess={onSuccess} auth={auth} />
                <div className="flex items-center justify-center gap-4 mt-2">
                    {PaymentMethodsInfo.mobileMoneyLogos.map((logo, index) => (
                        <img
                            key={index}
                            src={logo.logo}
                            alt={logo.name}
                            className="h-8 object-contain"
                        />
                    ))}
                </div>
            </TabsContent>

            <TabsContent value="card" className="mt-4 space-y-4">
                <div className="text-2xl font-bold text-center">
                    {pack.priceEuros} Fcfa
                    {/*<span className="text-sm text-gray-500 ml-2">*/}
                    {/*    ({pack.priceFCFA.toLocaleString()} FCFA)*/}
                    {/*</span>*/}
                </div>
                <PayPalPackButton pack={pack} onSuccess={onSuccess} />
                <div className="flex items-center justify-center gap-4 mt-2">
                    {PaymentMethodsInfo.cardLogos.map((logo, index) => (
                        <img
                            key={index}
                            src={logo.logo}
                            alt={logo.name}
                            className="h-6 object-contain"
                        />
                    ))}
                </div>
            </TabsContent>
        </Tabs>
    );
};

export default function Index({ auth, paypalConfig }) {
    const { toast } = useToast();
    const [error, setError] = useState(null);
    const { t } = useTranslation();

    if (!auth?.user) {
        window.location.href = '/login';
        return null;
    }

    const initialOptions = {
        "client-id": paypalConfig.clientId,
        currency: "XAF",
        intent: "capture",
    };

    const handlePaymentSuccess = (newBalance) => {
        auth.user.wallet_balance = newBalance;
        toast({
            title: t('payment.success.title'),
            description: t('payment.success.description'),
        });
        window.location.reload();
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={t('payment.pageTitle')} />
            {/*@ts-ignore*/}
            <PayPalScriptProvider options={initialOptions}>
                <div className="py-12">
                    <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <motion.div
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                className="mb-8"
                            >
                                <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-amber-500 to-purple-500 mb-6">
                                    <Coins className="h-10 w-10 text-white" />
                                </div>
                                <h1 className="text-4xl font-bold mb-4">
                                    <span className="bg-gradient-to-r from-amber-500 to-purple-500 text-transparent bg-clip-text">
                                        {t('payment.title')}
                                    </span>
                                </h1>
                                <p className="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                                    {t('payment.subtitle')}
                                </p>
                            </motion.div>

                            <div className="flex flex-wrap justify-center gap-4 mb-8">
                                <div className="flex items-center gap-2 px-4 py-2 bg-amber-100 dark:bg-amber-900/50 rounded-full">
                                    <Gift className="w-5 h-5 text-amber-500" />
                                    <span className="text-amber-700 dark:text-amber-300">
                                        {t('payment.features.bonus')}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 px-4 py-2 bg-purple-100 dark:bg-purple-900/50 rounded-full">
                                    <Smartphone className="w-5 h-5 text-purple-500" />
                                    <span className="text-purple-700 dark:text-purple-300">
                                        {t('payment.features.mobile')}
                                    </span>
                                </div>
                                <div className="flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/50 rounded-full">
                                    <CreditCard className="w-5 h-5 text-blue-500" />
                                    <span className="text-blue-700 dark:text-blue-300">
                                        {t('payment.features.card')}
                                    </span>
                                </div>
                            </div>

                            {error && (
                                <Alert variant="destructive" className="max-w-2xl mx-auto mb-8">
                                    <AlertCircle className="h-4 w-4" />
                                    <AlertDescription>{error}</AlertDescription>
                                </Alert>
                            )}
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            {TOKEN_PACKS.map((pack, index) => (
                                <motion.div
                                    key={pack.id}
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: index * 0.1 }}
                                >
                                    <Card className={cn(
                                        "relative overflow-hidden",
                                        "transition-all duration-300 hover:scale-105 hover:shadow-lg",
                                        pack.mostPopular && "ring-2 ring-amber-500 dark:ring-amber-400"
                                    )}>
                                        {pack.mostPopular && (
                                            <div className="absolute top-0 right-0">
                                                <div className="bg-gradient-to-r from-amber-500 to-purple-500 text-white px-4 py-1 rounded-bl-lg text-sm font-medium">
                                                    {t(`payment.popularityText.${pack.popularityText}`)}
                                                </div>
                                            </div>
                                        )}
                                        <CardContent className="p-6">
                                            <div className="mb-6">
                                                <div className={cn(
                                                    "w-16 h-16 rounded-xl bg-gradient-to-r flex items-center justify-center mb-4",
                                                    pack.color
                                                )}>
                                                    <Coins className="w-8 h-8 text-white" />
                                                </div>
                                                <div className="flex items-center justify-between mb-2">
                                                    <span className="text-xl font-semibold">
                                                        {pack.tokens} {t('payment.tokens')}
                                                    </span>
                                                    {pack.bonusTokens > 0 && (
                                                        <span className="text-green-500 dark:text-green-400 font-medium animate-pulse">
                                                            +{pack.bonusTokens} {t('payment.bonus')}
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="text-sm text-gray-500 dark:text-gray-400">
                                                    {t('payment.total')}: {pack.tokens + pack.bonusTokens} {t('payment.tokens')}
                                                </div>
                                                {/* <div className="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    {t('payment.unitPrice')}: {(pack.priceEuros / (pack.tokens + pack.bonusTokens) * 10).toFixed(2)} Fcfa / 10 {t('payment.tokens')}
                                                </div> */}

                                                <div className="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                                    <PaymentTabs
                                                        pack={pack}
                                                        onSuccess={handlePaymentSuccess}
                                                        auth={auth}
                                                    />
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                </motion.div>
                            ))}
                        </div>

                        <div className="mt-12 space-y-4">
                            <div className="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-3xl mx-auto">
                                <h2 className="text-lg font-semibold mb-4">{t('payment.importantInfo.title')}</h2>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2">
                                        <h3 className="font-medium">{t('payment.importantInfo.mobileMoney.title')}</h3>
                                        <ul className="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                            <li>• {t('payment.importantInfo.mobileMoney.instant')}</li>
                                            <li>• {t('payment.importantInfo.mobileMoney.accepted')}</li>
                                            <li>• {t('payment.importantInfo.mobileMoney.pricing')}</li>
                                        </ul>
                                    </div>
                                    <div className="space-y-2">
                                        <h3 className="font-medium">{t('payment.importantInfo.bankCard.title')}</h3>
                                        <ul className="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                            <li>• {t('payment.importantInfo.bankCard.secure')}</li>
                                            <li>• {t('payment.importantInfo.bankCard.accepted')}</li>
                                            <li>• {t('payment.importantInfo.bankCard.pricing')}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div className="text-center text-sm text-gray-500 dark:text-gray-400">
                                <p>{t('payment.footer.immediate')}</p>
                                <p className="mt-2">{t('payment.footer.secure')}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </PayPalScriptProvider>
        </AuthenticatedLayout>
    );
}