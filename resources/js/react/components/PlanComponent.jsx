import React, { useEffect, useState } from "react";
import { GlobalAPIcall } from "../config/ApiUtils";
import '../../../css/app.css';
import { Redirect } from '@shopify/app-bridge/actions';
import { useAppBridge } from "@shopify/app-bridge-react";

export const PlanComponent = () => {
    const app = useAppBridge();
    const [ActivePlan, setActivePlan] = useState(true);
    const [YearlyPlan, setYearlyPlan] = useState(true);

    const planapi = async (e) => {

        console.log(e);
        var plan = new FormData();
        plan.append("plan", e)
        var res = await GlobalAPIcall('POST', '/SubscriptionPlan', plan);
        const data = await res.confirmationUrl;
        console.log(data);
        const redirect = Redirect.create(app);
        console.log(redirect);
        redirect.dispatch(Redirect.Action.REMOTE, data);

    }
    const chargedata = async () => {
        var res = await GlobalAPIcall('GET', '/getchargeid');

        console.log(res);
        if (res.charge_id && res.plan == 1) {
            setActivePlan(false)
        } else if (res.charge_id && res.plan == 2) {
            setYearlyPlan(false);
        }
        else {
            setActivePlan(true);
            setYearlyPlan(true);
        }
    }

    useEffect(() => {
        chargedata()
    }, [])
    return (
        <>
            <div className="row">
                <div className="col-md-4"></div>
                <div className="col-md-4">
                    <h1 className="chooseplan">CHOOSE PLAN</h1>
                </div>
                <div className="col-md-4"></div>
            </div>
            <div className="row">
                <div className="col-md-1"></div>
                <div className="col-md-5">
                    <div className="card text-center">
                        <div className="card-header">
                            Monthly
                        </div>
                        <div className="card-body">
                            <h5 className="card-title">$2.99/<sub>Month</sub></h5>
                            <p className="card-text">All Features</p>
                            <div className="card-btn">
                                {ActivePlan ? <button value={1} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button> : <button href="#" className="disabled" disabled>Current Active Plan</button>}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-5">
                    <div className="card text-center">
                        <div className="card-header">
                            Yearly @20% Discount
                        </div>
                        <div className="card-body">
                            <h5 className="card-title">$29.99/<sub>Month</sub></h5>
                            <p className="card-text">All Features</p>
                            <div className="card-btn">
                                {YearlyPlan ? <button value={2} onClick={(e) => planapi(e.target.value)} className="upgrade">Upgrade</button> : <button href="#" className="disabled" disabled>Current Active Plan</button>}
                            </div>
                        </div>
                    </div>
                </div>
                <div className="col-md-1"></div>
            </div>

        </>
    );
}

export default PlanComponent;