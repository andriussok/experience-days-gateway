import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';
import React, { useState, useEffect } from 'react';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { getSetting } = window.wc.wcSettings;

const settings = getSetting('experience_days_data', {});

const label = decodeEntities(settings.title) || __('Experience Days Payment', 'experience-days-gateway');

const ExperienceDaysPaymentMethod = (props) => {
    const { eventRegistration, emitResponse } = props;
    const { onPaymentProcessing } = eventRegistration;
    const [voucherCode, setVoucherCode] = useState('');

    useEffect(() => {
        const unsubscribe = onPaymentProcessing(async () => {
            const customDataIsValid = voucherCode.trim() !== '';

            if (customDataIsValid) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            experienceDaysVoucher: voucherCode,
                        },
                    },
                };
            }

            return {
                type: emitResponse.responseTypes.ERROR,
                message: __('Voucher code is required', 'experience-days-gateway'),
            };
        });

        return () => {
            unsubscribe();
        };
    }, [voucherCode, emitResponse.responseTypes, onPaymentProcessing]);

    return (
        <div>
            <TextControl
                label={__('Voucher Code', 'experience-days-gateway')}
                value={voucherCode}
                onChange={(value) => setVoucherCode(value)}
                placeholder={__('Enter your voucher code', 'experience-days-gateway')}
            />
        </div>
    );
};

const Label = (props) => {
    const { PaymentMethodLabel } = props.components;
    return <PaymentMethodLabel text={label} />;
};

registerPaymentMethod({
    name: 'experience_days_gateway',
    label: <Label />,
    content: <ExperienceDaysPaymentMethod />,
    edit: <ExperienceDaysPaymentMethod />,
    canMakePayment: () => true,
    ariaLabel: label,
    supports: {
        features: settings.supports,
    },
});
